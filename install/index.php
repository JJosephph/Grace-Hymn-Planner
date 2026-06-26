<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lockFile = BASE_PATH . '/install.lock';
$configDir = BASE_PATH . '/config';
$uploadDir = BASE_PATH . '/public/uploads';
$errors = [];
$success = false;
$publicUrlPrefix = defined('PUBLIC_URL_PREFIX')
    ? rtrim(PUBLIC_URL_PREFIX, '/')
    : (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/public/') !== false ? '' : '/public');
$queryRouteMode = (defined('ROUTE_URL_MODE') && ROUTE_URL_MODE === 'query')
    || strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/install/') !== false;
$baseTables = [
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

function install_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function check_writable_dir(string $path): bool
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
    return is_writable($path);
}

function slugify_tag_code(string $name): string
{
    return 'tag_' . substr(md5($name), 0, 12);
}

function sanitize_table_prefix(string $prefix): string
{
    $prefix = trim($prefix);
    if ($prefix === '') {
        throw new InvalidArgumentException('表前缀不能为空。');
    }

    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,30}$/', $prefix)) {
        throw new InvalidArgumentException('表前缀只能包含英文字母、数字和下划线，并且必须以字母开头。');
    }

    return $prefix;
}

function prefix_sql_tables(string $sql, string $prefix, array $tables): string
{
    if ($prefix === '') {
        return $sql;
    }

    usort($tables, function (string $a, string $b): int {
        return strlen($b) <=> strlen($a);
    });

    foreach ($tables as $table) {
        $sql = preg_replace('/(?<![A-Za-z0-9_])' . preg_quote($table, '/') . '(?![A-Za-z0-9_])/', $prefix . $table, $sql);
    }

    return $sql;
}

function install_route_url(string $path, bool $queryRouteMode): string
{
    if (!$queryRouteMode) {
        return '/' . ltrim($path, '/');
    }

    $route = '/' . ltrim($path, '/');
    return $route === '/' ? '/index.php' : '/index.php?r=' . rawurlencode($route);
}

function split_sql_statements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $inString = false;
    $quote = '';

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $buffer .= $char;

        if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i - 1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $quote = $char;
            } elseif ($quote === $char) {
                $inString = false;
            }
        }

        if ($char === ';' && !$inString) {
            $statements[] = trim($buffer);
            $buffer = '';
        }
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

$checks = [
    'PHP 版本 >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO 扩展' => extension_loaded('pdo'),
    'pdo_mysql 扩展' => extension_loaded('pdo_mysql'),
    'Session 可用' => function_exists('session_start'),
    '文件上传开启' => (bool) ini_get('file_uploads'),
    'config 目录可写' => check_writable_dir($configDir),
    'public/uploads 目录可写' => check_writable_dir($uploadDir),
];

if (file_exists($lockFile)) {
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>安装已锁定</title><body style="font-family:sans-serif;background:#F7F4EE;padding:40px"><h1>安装器已锁定</h1><p>检测到 install.lock，系统禁止重复安装。</p><p><a href="/">返回系统</a></p></body>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($checks as $label => $passed) {
        if (!$passed) {
            $errors[] = $label . ' 未通过';
        }
    }

    $host = trim($_POST['host'] ?? '127.0.0.1');
    $port = trim($_POST['port'] ?? '3306');
    $database = trim($_POST['database'] ?? '');
    $username = trim($_POST['db_username'] ?? '');
    $password = (string) ($_POST['db_password'] ?? '');
    $tablePrefix = trim($_POST['table_prefix'] ?? 'ghp_');
    $adminUsername = trim($_POST['admin_username'] ?? 'admin');
    $adminPassword = (string) ($_POST['admin_password'] ?? '');
    $adminNickname = trim($_POST['admin_nickname'] ?? '管理员');

    if ($database === '' || $username === '' || $adminUsername === '' || $adminPassword === '') {
        $errors[] = '数据库名、数据库用户、管理员用户名和管理员密码不能为空。';
    }

    try {
        $tablePrefix = sanitize_table_prefix($tablePrefix);
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }

    if (!$errors) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $schemaSql = prefix_sql_tables(file_get_contents(BASE_PATH . '/install/schema.sql'), $tablePrefix, $baseTables);
            foreach (split_sql_statements($schemaSql) as $statement) {
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }

            $now = date('Y-m-d H:i:s');
            $groupStmt = $pdo->prepare('INSERT INTO ' . $tablePrefix . 'tag_groups (name, code, description, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
            $tagStmt = $pdo->prepare('INSERT INTO ' . $tablePrefix . 'tags (group_id, name, code, description, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach (require BASE_PATH . '/install/seed_tags.php' as $group) {
                $groupStmt->execute([$group['name'], $group['code'], $group['description'], $group['sort_order'], $now, $now]);
                $groupId = (int) $pdo->lastInsertId();
                $sort = 10;
                foreach ($group['tags'] as $tagName) {
                    $tagStmt->execute([$groupId, $tagName, slugify_tag_code($group['code'] . '_' . $tagName), '', $sort, $now, $now]);
                    $sort += 10;
                }
            }

            $userStmt = $pdo->prepare('INSERT INTO ' . $tablePrefix . 'users (username, password_hash, nickname, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $userStmt->execute([$adminUsername, password_hash($adminPassword, PASSWORD_DEFAULT), $adminNickname, 'admin', 'active', $now, $now]);

            $databaseConfig = "<?php\n\nreturn [\n    'host' => " . var_export($host, true) . ",\n    'port' => " . var_export($port, true) . ",\n    'database' => " . var_export($database, true) . ",\n    'username' => " . var_export($username, true) . ",\n    'password' => " . var_export($password, true) . ",\n    'prefix' => " . var_export($tablePrefix, true) . ",\n];\n";
            $appConfig = "<?php\n\nreturn [\n    'name' => 'Grace Hymn Planner',\n    'locale' => 'zh_CN',\n    'timezone' => 'Asia/Shanghai',\n];\n";

            file_put_contents(BASE_PATH . '/config/database.php', $databaseConfig);
            file_put_contents(BASE_PATH . '/config/app.php', $appConfig);
            file_put_contents($lockFile, 'installed at ' . $now);
            $success = true;
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>安装 Grace Hymn Planner</title>
    <link rel="stylesheet" href="<?php echo install_e($publicUrlPrefix . '/assets/app.css'); ?>">
</head>
<body class="install-page">
<main class="install-wrap">
    <section class="install-card">
        <p class="eyebrow">Grace Hymn Planner</p>
        <h1>恩典圣诗选诗系统安装</h1>
        <p class="muted">完成环境检测、数据库初始化、默认标签写入和管理员创建。</p>

        <?php if ($success): ?>
            <div class="alert success">安装完成，可以进入系统登录。</div>
            <a class="btn primary" href="<?php echo install_e(install_route_url('/login', $queryRouteMode)); ?>">进入登录</a>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="alert danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo install_e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2>环境检测</h2>
            <div class="check-grid">
                <?php foreach ($checks as $label => $passed): ?>
                    <div class="check-item <?php echo $passed ? 'ok' : 'bad'; ?>">
                        <span><?php echo install_e($label); ?></span>
                        <strong><?php echo $passed ? '通过' : '失败'; ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="post" class="form-grid install-form">
                <h2>数据库配置</h2>
                <label>数据库地址<input name="host" value="<?php echo install_e($_POST['host'] ?? '127.0.0.1'); ?>"></label>
                <label>端口<input name="port" value="<?php echo install_e($_POST['port'] ?? '3306'); ?>"></label>
                <label>数据库名<input name="database" value="<?php echo install_e($_POST['database'] ?? ''); ?>" required></label>
                <label>数据库用户<input name="db_username" value="<?php echo install_e($_POST['db_username'] ?? ''); ?>" required></label>
                <label>数据库密码<input type="password" name="db_password" value="<?php echo install_e($_POST['db_password'] ?? ''); ?>"></label>
                <label>数据表前缀<input name="table_prefix" value="<?php echo install_e($_POST['table_prefix'] ?? 'ghp_'); ?>" required></label>

                <h2>管理员账号</h2>
                <label>用户名<input name="admin_username" value="<?php echo install_e($_POST['admin_username'] ?? 'admin'); ?>" required></label>
                <label>昵称<input name="admin_nickname" value="<?php echo install_e($_POST['admin_nickname'] ?? '管理员'); ?>"></label>
                <label>密码<input type="password" name="admin_password" required></label>
                <button class="btn primary" type="submit">开始安装</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
