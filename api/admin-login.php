<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s72_json(['error' => 'Method not allowed.'], 405);
$configured = (string)(getenv('ADMIN_PASSWORD') ?: '');
$password = (string)(s72_body()['password'] ?? '');
if ($configured === '') s72_json(['error' => 'Admin access is not configured. Set ADMIN_PASSWORD in .env.'], 503);
if (!hash_equals($configured, $password)) s72_json(['error' => 'Incorrect password.'], 401);
s72_admin_session();
session_regenerate_id(true);
$_SESSION['serum72_admin'] = true;
s72_json(['authenticated' => true]);
