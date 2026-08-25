<?php
declare(strict_types=1);

function s72_load_env(): void
{
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    $path = dirname(__DIR__) . '/.env';
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) putenv("{$key}={$value}");
    }
}

function s72_json(array $body, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit;
}

function s72_body(): array
{
    $decoded = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($decoded) ? $decoded : [];
}

function s72_admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('serum72_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function s72_admin_logged_in(): bool
{
    s72_admin_session();
    return !empty($_SESSION['serum72_admin']);
}

function s72_require_admin(): void
{
    if (!s72_admin_logged_in()) s72_json(['error' => 'Authentication required.'], 401);
}

function s72_archive_dir(): string
{
    $dir = dirname(__DIR__) . '/data/email-archive';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    return $dir;
}

function s72_archive_email(array $record): void
{
    $record += ['id' => bin2hex(random_bytes(8)), 'date' => gmdate('c'), 'status' => 'saved'];
    $path = s72_archive_dir() . '/' . gmdate('Ymd-His') . '-' . $record['id'] . '.json';
    file_put_contents($path, json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function s72_email_records(): array
{
    $records = [];
    foreach (glob(s72_archive_dir() . '/*.json') ?: [] as $file) {
        $item = json_decode((string) file_get_contents($file), true);
        if (is_array($item)) $records[] = $item;
    }
    usort($records, fn(array $a, array $b): int => strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')));
    return $records;
}

s72_load_env();
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:4321' || $origin === 'http://127.0.0.1:4321') {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Vary: Origin');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
