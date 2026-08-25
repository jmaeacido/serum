<?php
declare(strict_types=1);

require_once __DIR__ . '/admin-bootstrap.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:4321' || $origin === 'http://127.0.0.1:4321') {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function cleanLine(mixed $value, int $limit): string
{
    $value = preg_replace('/[\r\n]+/', ' ', trim((string) $value)) ?? '';
    return mb_substr($value, 0, $limit);
}

function sendWithBrevo(string $apiKey, string $recipient, string $fromEmail, string $fromName, array $payload): bool
{
    if (!function_exists('curl_init')) return false;
    $request = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($request, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'api-key: ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode([
            'sender' => ['name' => $fromName, 'email' => $fromEmail],
            'to' => [['email' => $recipient]],
            'replyTo' => ['name' => $payload['name'], 'email' => $payload['email']],
            'subject' => $payload['subject'],
            'textContent' => $payload['body'],
        ], JSON_UNESCAPED_SLASHES),
    ]);
    curl_exec($request);
    $status = (int) curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);
    return $status >= 200 && $status < 300;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'message' => 'Method not allowed.'], 405);
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) respond(['ok' => false, 'message' => 'Invalid request.'], 400);
if (!empty($input['website'])) respond(['ok' => true]);

$source = cleanLine($input['source'] ?? 'contact', 30);
$firstName = cleanLine($input['firstName'] ?? $input['name'] ?? '', 80);
$lastName = cleanLine($input['lastName'] ?? '', 80);
$name = trim($firstName . ' ' . $lastName);
$email = strtolower(cleanLine($input['email'] ?? '', 254));
$countryCode = cleanLine($input['countryCode'] ?? '', 12);
$phone = cleanLine($input['phone'] ?? '', 40);
$message = trim(mb_substr((string) ($input['message'] ?? ''), 0, 5000));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['ok' => false, 'message' => 'Enter your name and a valid email address.'], 422);
}
if ($source === 'contact' && $message === '') respond(['ok' => false, 'message' => 'Enter a message.'], 422);

$rateFile = sys_get_temp_dir() . '/serum72-contact-' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
$lastAttempt = is_file($rateFile) ? (int) file_get_contents($rateFile) : 0;
if ($lastAttempt > time() - 8) respond(['ok' => false, 'message' => 'Please wait a moment before sending another message.'], 429);
@file_put_contents($rateFile, (string) time(), LOCK_EX);

$recipient = getenv('SERUM72_CONTACT_TO') ?: 'info@serum72.com';
$fromEmail = getenv('SERUM72_MAIL_FROM') ?: 'website@serum72.com';
$fromName = 'Serum 72 Website';
$subject = $source === 'chat' ? 'New website chat lead' : 'New website contact message';
$body = "Source: {$source}\nName: {$name}\nEmail: {$email}\nPhone: " . trim($countryCode . ' ' . $phone) . "\n\nMessage:\n" . ($message ?: 'No message supplied.');
$mailPayload = compact('name', 'email', 'subject', 'body');

$brevoKey = getenv('SERUM72_BREVO_API_KEY') ?: '';
$sent = $brevoKey !== ''
    ? sendWithBrevo($brevoKey, $recipient, $fromEmail, $fromName, $mailPayload)
    : mail($recipient, $subject, $body, implode("\r\n", [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ]));

s72_archive_email([
    'direction' => 'inbound',
    'from' => $name . ' <' . $email . '>',
    'to' => $recipient,
    'subject' => $subject,
    'preview' => mb_substr($message ?: 'Contact details submitted without a message.', 0, 240),
    'status' => 'received',
    'source' => $source,
]);
s72_archive_email([
    'direction' => 'outbound',
    'from' => $fromName . ' <' . $fromEmail . '>',
    'to' => $recipient,
    'subject' => $subject,
    'preview' => mb_substr($body, 0, 240),
    'status' => $sent ? 'sent' : 'failed',
]);

if (!$sent) {
    error_log('Serum 72 contact delivery failed for ' . $email);
    respond(['ok' => false, 'message' => 'We could not send your message right now. Please try again or email info@serum72.com.'], 502);
}

respond(['ok' => true, 'message' => 'Thank you. We’ll get back to you as soon as possible.']);
