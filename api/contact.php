<?php
declare(strict_types=1);

require_once __DIR__ . '/admin-bootstrap.php';
require_once __DIR__ . '/mail.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:4321',
    'http://127.0.0.1:4321',
    'http://localhost:4322',
    'http://127.0.0.1:4322',
];
if (in_array($origin, $allowedOrigins, true)) {
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
$displayFirst = $firstName !== '' ? explode(' ', $firstName)[0] : explode(' ', $name)[0];

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['ok' => false, 'message' => 'Enter your name and a valid email address.'], 422);
}
if ($source === 'contact' && $message === '') respond(['ok' => false, 'message' => 'Enter a message.'], 422);

$rateFile = sys_get_temp_dir() . '/serum72-contact-' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
$lastAttempt = is_file($rateFile) ? (int) file_get_contents($rateFile) : 0;
if ($lastAttempt > time() - 8) respond(['ok' => false, 'message' => 'Please wait a moment before sending another message.'], 429);
@file_put_contents($rateFile, (string) time(), LOCK_EX);

$siteInbox = s72_contact_to();
$phoneLine = trim($countryCode . ' ' . $phone);
$offerCode = $source === 'popup' ? s72_popup_code() : null;

s72_archive_email([
    'direction' => 'inbound',
    'from' => $name . ' <' . $email . '>',
    'to' => $siteInbox,
    'subject' => match ($source) {
        'chat' => 'New website chat lead — ' . $name,
        'popup' => 'New 20% off lead — ' . $name,
        default => 'New website contact — ' . $name,
    },
    'preview' => mb_substr($message ?: ($source === 'popup' ? 'Requested 20% off first order via lead popup.' : 'Contact details submitted without a message.'), 0, 240),
    'status' => 'received',
    'source' => $source,
]);

$notify = s72_lead_notify_email($source, $name, $email, $phoneLine, $message, $offerCode);
$siteSent = s72_send_email([
    'toEmail' => $siteInbox,
    'toName' => 'Serum 72',
    'subject' => $notify['subject'],
    'html' => $notify['html'],
    'text' => $notify['text'],
    'replyToEmail' => $email,
    'replyToName' => $name,
]);

s72_archive_email([
    'direction' => 'outbound',
    'from' => s72_mail_from()['name'] . ' <' . s72_mail_from()['email'] . '>',
    'to' => $siteInbox,
    'subject' => $notify['subject'],
    'preview' => mb_substr($notify['text'], 0, 240),
    'status' => $siteSent ? 'sent' : 'failed',
    'source' => $source,
]);

$userSent = true;
if ($source === 'popup') {
    $welcome = s72_popup_welcome_email($displayFirst, (string) $offerCode);
    $userSent = s72_send_email([
        'toEmail' => $email,
        'toName' => $name,
        'subject' => $welcome['subject'],
        'html' => $welcome['html'],
        'text' => $welcome['text'],
        'replyToEmail' => $siteInbox,
        'replyToName' => 'Serum 72',
    ]);
    s72_archive_email([
        'direction' => 'outbound',
        'from' => s72_mail_from()['name'] . ' <' . s72_mail_from()['email'] . '>',
        'to' => $name . ' <' . $email . '>',
        'subject' => $welcome['subject'],
        'preview' => mb_substr($welcome['text'], 0, 240),
        'status' => $userSent ? 'sent' : 'failed',
        'source' => 'popup-welcome',
    ]);
}

if (!$siteSent || !$userSent) {
    error_log('Serum 72 contact delivery failed for ' . $email . ' site=' . ($siteSent ? '1' : '0') . ' user=' . ($userSent ? '1' : '0'));
    respond(['ok' => false, 'message' => 'We could not send your message right now. Please try again or email info@serum72.com.'], 502);
}

if ($source === 'popup') {
    respond(['ok' => true, 'message' => 'You’re in — check your inbox for your 20% off code.']);
}

respond(['ok' => true, 'message' => 'Thank you. We’ll get back to you as soon as possible.']);
