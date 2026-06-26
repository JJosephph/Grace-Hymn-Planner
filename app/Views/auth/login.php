<?php use App\Core\Csrf; ?>
<main class="auth-page">
    <section class="auth-card">
        <p class="eyebrow">Grace Hymn Planner</p>
        <h1>恩典圣诗选诗系统</h1>
        <p class="muted">登录后进入司会选诗工作台。</p>
        <?php if ($message = flash('error')): ?>
            <div class="alert danger"><?php echo e($message); ?></div>
        <?php endif; ?>
        <form method="post" action="/login" class="form-grid">
            <?php echo Csrf::input(); ?>
            <label>用户名<input name="username" autocomplete="username" required autofocus></label>
            <label>密码<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="btn primary full" type="submit">登录</button>
        </form>
    </section>
</main>

