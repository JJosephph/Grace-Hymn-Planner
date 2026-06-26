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
    if ($path === '') {
        $path = '/';
    }

    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    if (strpos($path, '/index.php') === 0 || (defined('ROUTE_URL_MODE') && ROUTE_URL_MODE !== 'query')) {
        return '/' . ltrim($path, '/');
    }

    $parts = parse_url($path);
    $route = '/' . ltrim($parts['path'] ?? '/', '/');
    if ($route === '//') {
        $route = '/';
    }

    if ($route === '/') {
        return '/index.php';
    }

    $query = $parts['query'] ?? '';
    return '/index.php?r=' . rawurlencode($route) . ($query !== '' ? '&' . $query : '');
}

function rewrite_route_links(string $html): string
{
    if (!defined('ROUTE_URL_MODE') || ROUTE_URL_MODE !== 'query') {
        return $html;
    }

    $routePrefixes = ['login', 'logout', 'search', 'hymns', 'tunes', 'tags', 'plans', 'files'];

    return preg_replace_callback('/\b(href|action|data-drawer-url)="(\/[^"]*)"/', function (array $matches) use ($routePrefixes): string {
        $attribute = $matches[1];
        $path = $matches[2];
        $trimmed = ltrim($path, '/');

        if ($path === '/' || in_array(strtok($trimmed, '/?'), $routePrefixes, true)) {
            return $attribute . '="' . e(url($path)) . '"';
        }

        return $matches[0];
    }, $html);
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
