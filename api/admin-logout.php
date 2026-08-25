<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') s72_json(['error' => 'Method not allowed.'], 405);
s72_admin_session();
$_SESSION = [];
session_destroy();
s72_json(['authenticated' => false]);
