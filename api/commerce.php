<?php
declare(strict_types=1);

function s72_root(): string { return dirname(__DIR__); }

function s72_load_env(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    $path = s72_root() . '/.env';
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name); $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) $value = substr($value, 1, -1);
        if ($name !== '' && getenv($name) === false) putenv($name . '=' . $value);
    }
}
s72_load_env();

function s72_json(int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit;
}
function s72_body(): array { $value = json_decode(file_get_contents('php://input') ?: '', true); return is_array($value) ? $value : []; }
function s72_data_dir(string $name): string {
    $path = s72_root() . '/data/' . trim($name, '/');
    if (!is_dir($path) && !mkdir($path, 0770, true) && !is_dir($path)) throw new RuntimeException('Unable to initialize order storage.');
    return $path;
}
function s72_catalog(): array {
    return [
        'fortify-day-cream' => ['name'=>'Fortify Day Cream','price'=>4999,'subscriptionPrice'=>4499,'image'=>'public/assets/products/fortify-day-cream.jpg'],
        'night-cream' => ['name'=>'Night Cream','price'=>4999,'subscriptionPrice'=>4499,'image'=>'public/assets/products/night-cream.jpg'],
        'premium-cleanser' => ['name'=>'Premium Cleanser','price'=>3299,'subscriptionPrice'=>2969,'image'=>'public/assets/products/premium-cleanser.jpg'],
        'radiance-serum' => ['name'=>'Radiance Serum','price'=>5399,'subscriptionPrice'=>4859,'image'=>'public/assets/products/radiance-serum.jpg'],
    ];
}
function s72_site_url(): string {
    $url = rtrim((string)(getenv('APP_URL') ?: ''), '/');
    if ($url !== '') return $url;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'serum72.test');
}
