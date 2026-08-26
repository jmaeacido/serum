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
        'night-cream' => ['name'=>'Night Cream','price'=>6999,'subscriptionPrice'=>6399,'image'=>'public/assets/products/night-cream.png'],
        'day-cream' => ['name'=>'Day Cream','price'=>4999,'subscriptionPrice'=>4499,'image'=>'public/assets/products/day-cream.png'],
        'serum' => ['name'=>'Serum','price'=>5999,'subscriptionPrice'=>5399,'image'=>'public/assets/products/serum.png'],
        'mineral-sunscreen' => ['name'=>'Mineral Sunscreen','price'=>3999,'subscriptionPrice'=>3599,'image'=>'public/assets/products/mineral-sunscreen.png'],
        'dual-action-cleanser' => ['name'=>'Dual Action Cleanser','price'=>3499,'subscriptionPrice'=>3149,'image'=>'public/assets/products/dual-action-cleanser.webp'],
    ];
}
function s72_site_url(): string {
    $url = rtrim((string)(getenv('APP_URL') ?: ''), '/');
    if ($url !== '') return $url;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'serum72.test');
}
