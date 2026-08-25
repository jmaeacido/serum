<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:4321' || $origin === 'http://127.0.0.1:4321') {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_name('serum72_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $query = database()->prepare('SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1');
    $query->execute([(int) $_SESSION['user_id']]);
    return $query->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(['ok' => true, 'csrf' => csrfToken(), 'user' => currentUser()]);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond(['ok' => false, 'message' => 'Invalid request.'], 400);
}

$csrf = (string) ($input['csrf'] ?? '');
if (!hash_equals(csrfToken(), $csrf)) {
    respond(['ok' => false, 'message' => 'Your session expired. Refresh and try again.'], 419);
}

$action = (string) ($input['action'] ?? '');
if ($action === 'logout') {
    $_SESSION = [];
    session_regenerate_id(true);
    respond(['ok' => true, 'csrf' => csrfToken(), 'user' => null]);
}

$_SESSION['auth_attempts'] = array_values(array_filter(
    $_SESSION['auth_attempts'] ?? [],
    static fn (int $time): bool => $time > time() - 300
));
if (count($_SESSION['auth_attempts']) >= 12) {
    respond(['ok' => false, 'message' => 'Too many attempts. Please wait a few minutes.'], 429);
}
$_SESSION['auth_attempts'][] = time();

$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    respond(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
}

if ($action === 'register') {
    $name = trim((string) ($input['name'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        respond(['ok' => false, 'message' => 'Name must be between 2 and 100 characters.'], 422);
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        respond(['ok' => false, 'message' => 'Use at least 8 characters with a letter and a number.'], 422);
    }

    try {
        $insert = database()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $error) {
        if (($error->errorInfo[1] ?? null) === 1062) {
            respond(['ok' => false, 'message' => 'An account already exists for this email.'], 409);
        }
        throw $error;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) database()->lastInsertId();
    $_SESSION['auth_attempts'] = [];
    respond(['ok' => true, 'csrf' => csrfToken(), 'user' => currentUser()]);
}

if ($action === 'login') {
    $query = database()->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $query->execute([$email]);
    $user = $query->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        respond(['ok' => false, 'message' => 'Email or password is incorrect.'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['auth_attempts'] = [];
    respond(['ok' => true, 'csrf' => csrfToken(), 'user' => currentUser()]);
}

respond(['ok' => false, 'message' => 'Unknown action.'], 400);
