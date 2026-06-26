<div class="page-head">
    <div>
        <p class="eyebrow">曲调详情</p>
        <h1><?php echo e($tune['tune_name']); ?></h1>
        <p class="muted"><?php echo e($tune['tune_name_en']); ?></p>
    </div>
    <a class="btn primary" href="/tunes/<?php echo (int) $tune['id']; ?>/edit">编辑曲调</a>
</div>

<section class="detail-layout">
    <article class="card">
        <h2>曲调资料</h2>
        <dl class="info-list">
            <dt>曲作者</dt><dd><?php echo e($tune['composer']); ?></dd>
            <dt>韵律</dt><dd><?php echo e($tune['meter']); ?></dd>
            <dt>常用调号</dt><dd><?php echo e($tune['key_signature']); ?></dd>
            <dt>速度</dt><dd><?php echo e($tune['tempo']); ?></dd>
            <dt>备注</dt><dd><?php echo nl2br_e($tune['note']); ?></dd>
        </dl>
    </article>
    <aside class="card">
        <h2>曲调附件</h2>
        <div class="empty-state">MVP 已预留曲调附件表，下一步可接上传入口。</div>
    </aside>
</section>

<section class="card">
    <div class="section-head"><h2>使用该曲调的诗歌</h2><a href="/hymns?tune_id=<?php echo (int) $tune['id']; ?>">筛选圣诗库</a></div>
    <div class="compact-list">
        <?php foreach ($tune['hymns'] as $hymn): ?>
            <a href="/hymns/<?php echo (int) $hymn['id']; ?>">
                <strong><?php echo e($hymn['title_cn']); ?></strong>
                <span><?php echo e($hymn['first_line']); ?></span>
                <?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?>
            </a>
        <?php endforeach; ?>
        <?php if (!$tune['hymns']): ?><div class="empty-state">还没有圣诗关联这个曲调。</div><?php endif; ?>
    </div>
</section>

