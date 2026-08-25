<?php
declare(strict_types=1);

function database(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('SERUM72_DB_HOST') ?: '127.0.0.1';
    $port = getenv('SERUM72_DB_PORT') ?: '3306';
    $name = getenv('SERUM72_DB_NAME') ?: 'serum72';
    $user = getenv('SERUM72_DB_USER') ?: 'root';
    $pass = getenv('SERUM72_DB_PASS') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
