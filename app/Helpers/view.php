<?php

function asset(string $path): string
{
    return public_url('/assets/' . ltrim($path, '/'));
}

function public_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $prefix = defined('PUBLIC_URL_PREFIX') ? rtrim(PUBLIC_URL_PREFIX, '/') : '';

    if ($prefix !== '' && strpos($path, $prefix . '/') === 0) {
        return $path;
    }

    return $prefix . $path;
}

function public_file_url(?string $path): string
{
    if (!$path) {
        return '';
    }

    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    return public_url($path);
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
