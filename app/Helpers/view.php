<?php

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function is_active_nav(string $prefix): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($prefix === '/') {
        return $path === '/' ? 'active' : '';
    }

    return ($path === $prefix || strpos($path, rtrim($prefix, '/') . '/') === 0) ? 'active' : '';
}

function flash(?string $key = null)
{
    if (!isset($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }

    if ($key === null) {
        $all = $_SESSION['_flash'];
        $_SESSION['_flash'] = [];
        return $all;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}
