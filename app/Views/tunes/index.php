<div class="page-head">
    <div>
        <p class="eyebrow">曲调 / 旋律</p>
        <h1>管理同曲不同词</h1>
    </div>
    <a class="btn primary" href="/tunes/create">新增曲调</a>
</div>

<section class="card-grid">
    <?php foreach ($tunes as $tune): ?>
        <article class="card tune-card">
            <p class="eyebrow"><?php echo e($tune['meter'] ?: '未填写韵律'); ?></p>
            <h2><a href="/tunes/<?php echo (int) $tune['id']; ?>"><?php echo e($tune['tune_name']); ?></a></h2>
            <p class="muted"><?php echo e($tune['tune_name_en']); ?></p>
            <div class="meta-row">
                <span><?php echo e($tune['composer'] ?: '曲作者待补'); ?></span>
                <span><?php echo (int) $tune['hymn_count']; ?> 首诗歌使用</span>
            </div>
            <a class="btn" href="/tunes/<?php echo (int) $tune['id']; ?>">查看同曲诗歌</a>
        </article>
    <?php endforeach; ?>
    <?php if (!$tunes): ?><div class="empty-state card">还没有曲调资料。</div><?php endif; ?>
</section>

