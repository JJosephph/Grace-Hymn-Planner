<?php
use App\Core\Auth;
use App\Core\Csrf;

$user = Auth::user();
$isAuthPage = ($view ?? '') === 'auth/login';
$messages = $isAuthPage ? [] : flash();
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(($title ?? 'Grace Hymn Planner') . '｜Grace Hymn Planner'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('app.css')); ?>">
</head>
<body>
<?php if ($isAuthPage): ?>
    <?php require $viewFile; ?>
<?php else: ?>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="/">
                <span class="brand-mark">G</span>
                <span><strong>Grace Hymn Planner</strong><small>恩典圣诗选诗系统</small></span>
            </a>
            <button class="command-trigger" type="button" data-command-open>Ctrl + K 搜索圣诗、经文、标签</button>
            <div class="top-actions">
                <?php if (Auth::canEdit()): ?>
                    <a class="btn primary" href="/hymns/create">新增圣诗</a>
                <?php endif; ?>
                <span class="user-pill"><?php echo e($user['nickname'] ?? ''); ?></span>
                <form method="post" action="/logout">
                    <?php echo Csrf::input(); ?>
                    <button class="btn ghost" type="submit">退出</button>
                </form>
            </div>
        </header>

        <aside class="sidenav">
            <nav>
                <a class="<?php echo is_active_nav('/'); ?>" href="/">工作台</a>
                <a class="<?php echo is_active_nav('/hymns'); ?>" href="/hymns">圣诗库</a>
                <a class="<?php echo is_active_nav('/plans'); ?>" href="/plans">本周选诗</a>
                <a class="<?php echo is_active_nav('/tunes'); ?>" href="/tunes">曲调</a>
                <?php if (Auth::canEdit()): ?>
                    <a class="<?php echo is_active_nav('/tags'); ?>" href="/tags">标签</a>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="workspace">
            <?php foreach ($messages as $type => $message): ?>
                <div class="toast <?php echo e($type); ?>"><?php echo e($message); ?></div>
            <?php endforeach; ?>
            <?php require $viewFile; ?>
        </main>

        <nav class="bottom-nav">
            <a href="/">工作台</a>
            <a href="/hymns">圣诗</a>
            <a href="/plans">选诗</a>
            <a href="/tunes">曲调</a>
        </nav>
    </div>

    <div class="modal command-modal" data-command-modal hidden>
        <div class="modal-panel">
            <div class="modal-head">
                <strong>全局搜索</strong>
                <button class="icon-btn" type="button" data-command-close>×</button>
            </div>
            <input class="command-input" type="search" placeholder="输入诗歌名、歌词、经文、标签或曲调" data-command-input>
            <div class="command-results" data-command-results></div>
        </div>
    </div>
<?php endif; ?>
<script src="<?php echo e(asset('app.js')); ?>"></script>
</body>
</html>
