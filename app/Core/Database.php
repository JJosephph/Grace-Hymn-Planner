<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;
    private static ?array $config = null;
    private static array $tables = [
        'users',
        'tunes',
        'hymns',
        'tag_groups',
        'tags',
        'hymn_tag',
        'hymn_files',
        'tune_files',
        'service_plans',
        'service_plan_items',
        'settings',
        'activity_logs',
    ];

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $configFile = BASE_PATH . '/config/database.php';
        if (!file_exists($configFile)) {
            header('Location: /install');
            exit;
        }

        $config = self::config();
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'] ?? 3306,
            $config['database']
        );

        self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    public static function config(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $configFile = BASE_PATH . '/config/database.php';
        if (!file_exists($configFile)) {
            header('Location: /install');
            exit;
        }

        self::$config = require $configFile;
        self::$config['prefix'] = self::sanitizePrefix(self::$config['prefix'] ?? 'ghp_');
        return self::$config;
    }

    public static function prefix(): string
    {
        $config = self::config();
        return $config['prefix'];
    }

    public static function table(string $name): string
    {
        return self::prefix() . $name;
    }

    public static function prefixSql(string $sql): string
    {
        $tables = self::$tables;
        usort($tables, function (string $a, string $b): int {
            return strlen($b) <=> strlen($a);
        });

        foreach ($tables as $table) {
            $prefixed = self::table($table);
            $sql = preg_replace('/(?<![A-Za-z0-9_])' . preg_quote($table, '/') . '(?![A-Za-z0-9_])/', $prefixed, $sql);
        }

        return $sql;
    }

    public static function sanitizePrefix(string $prefix): string
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            throw new \InvalidArgumentException('表前缀不能为空。');
        }

        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,30}$/', $prefix)) {
            throw new \InvalidArgumentException('表前缀只能包含英文字母、数字和下划线，并且必须以字母开头。');
        }

        return $prefix;
    }
}
