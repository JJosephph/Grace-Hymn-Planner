<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('PUBLIC_URL_PREFIX')) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    define('PUBLIC_URL_PREFIX', strpos($scriptName, '/public/') !== false ? '/public' : '');
}

require BASE_PATH . '/app/Helpers/escape.php';
require BASE_PATH . '/app/Helpers/view.php';
require BASE_PATH . '/app/Helpers/completeness.php';
require BASE_PATH . '/app/Helpers/upload.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

App\Core\App::run();
