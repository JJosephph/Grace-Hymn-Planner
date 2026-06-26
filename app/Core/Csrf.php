<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function input(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    public static function verify(): void
    {
        $posted = $_POST['_token'] ?? '';
        if (!is_string($posted) || !hash_equals(self::token(), $posted)) {
            http_response_code(419);
            echo 'CSRF token mismatch.';
            exit;
        }
    }
}

