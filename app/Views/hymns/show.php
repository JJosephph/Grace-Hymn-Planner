<?php use App\Core\Auth; use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">圣诗详情</p>
        <h1><?php echo e($hymn['title_cn']); ?></h1>
        <p class="muted"><?php echo e($hymn['title_en']); ?></p>
    </div>
    <div class="head-actions">
        <?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?>
        <?php if (Auth::canEdit()): ?><a class="btn primary" href="/hymns/<?php echo (int) $hymn['id']; ?>/edit">编辑</a><?php endif; ?>
    </div>
</div>

<?php $missingLabels = missingFieldLabels($hymn['missing_fields']); ?>
<?php if ($missingLabels): ?>
    <div class="alert warning">这首诗歌资料尚未完整：<?php echo e(implode('、', $missingLabels)); ?>。</div>
<?php endif; ?>

<section class="detail-layout">
    <article class="card detail-main">
        <div class="section-head">
            <h2>完整歌词</h2>
            <button class="btn" type="button" data-copy="<?php echo e($hymn['lyrics']); ?>">复制歌词</button>
        </div>
        <div class="lyrics"><?php echo $hymn['lyrics'] ? nl2br_e($hymn['lyrics']) : '<span class="muted">尚未录入歌词。</span>'; ?></div>
    </article>

    <aside class="card detail-side">
        <h2>诗歌信息</h2>
        <dl class="info-list">
            <dt>第一句</dt><dd><?php echo e($hymn['first_line']); ?></dd>
            <dt>曲调</dt><dd><?php echo $hymn['tune_id'] ? '<a href="/tunes/' . (int) $hymn['tune_id'] . '">' . e($hymn['tune_name']) . '</a>' : '未关联'; ?></dd>
            <dt>相关经文</dt><dd><?php echo e($hymn['scripture_refs']); ?></dd>
            <dt>熟悉度</dt><dd><?php echo (int) $hymn['familiarity']; ?></dd>
            <dt>难度</dt><dd><?php echo (int) $hymn['difficulty']; ?></dd>
        </dl>
        <div class="tag-cloud">
            <?php foreach ($hymn['tags'] as $tag): ?><span class="tag-chip"><?php echo e($tag['name']); ?></span><?php endforeach; ?>
        </div>
        <?php if ($latestPlan): ?>
            <form method="post" action="/plans/<?php echo (int) $latestPlan['id']; ?>/items">
                <?php echo Csrf::input(); ?>
                <input type="hidden" name="hymn_id" value="<?php echo (int) $hymn['id']; ?>">
                <button class="btn primary full" type="submit">加入本周候选</button>
            </form>
        <?php endif; ?>
    </aside>
</section>

<section class="two-col">
    <div class="card">
        <div class="section-head"><h2>PPT 简版歌词</h2><button class="btn" type="button" data-copy="<?php echo e($hymn['ppt_lyrics']); ?>">复制 PPT 歌词</button></div>
        <div class="lyrics compact"><?php echo $hymn['ppt_lyrics'] ? nl2br_e($hymn['ppt_lyrics']) : '<span class="muted">尚未录入 PPT 简版歌词。</span>'; ?></div>
    </div>
    <div class="card">
        <h2>神学摘要与使用建议</h2>
        <p><?php echo nl2br_e($hymn['doctrine_summary']); ?></p>
        <p class="muted"><?php echo nl2br_e($hymn['usage_note']); ?></p>
    </div>
</section>

<section class="card">
    <div class="section-head"><h2>同曲诗歌</h2><span class="muted">帮助判断熟悉旋律下的不同歌词版本</span></div>
    <div class="compact-list">
        <?php foreach ($hymn['same_tune_hymns'] as $item): ?>
            <a href="/hymns/<?php echo (int) $item['id']; ?>"><strong><?php echo e($item['title_cn']); ?></strong><span><?php echo e($item['first_line']); ?></span></a>
        <?php endforeach; ?>
        <?php if (!$hymn['same_tune_hymns']): ?><div class="empty-state">暂时没有同曲诗歌。</div><?php endif; ?>
    </div>
</section>

<section class="card">
    <h2>附件预览</h2>
    <div class="file-grid">
        <?php foreach ($hymn['files'] as $file): ?>
            <article class="file-item">
                <?php if ($file['file_type'] === 'score_image'): ?><img src="<?php echo e($file['file_path']); ?>" alt="<?php echo e($file['original_name']); ?>"><?php endif; ?>
                <?php if ($file['file_type'] === 'score_pdf'): ?><embed src="<?php echo e($file['file_path']); ?>" type="application/pdf"><?php endif; ?>
                <strong><?php echo e($file['original_name']); ?></strong>
                <a class="btn" href="/files/<?php echo (int) $file['id']; ?>/download">下载</a>
            </article>
        <?php endforeach; ?>
        <?php if (!$hymn['files']): ?><div class="empty-state">还没有上传歌谱或附件。</div><?php endif; ?>
    </div>
</section>

<?php if (Auth::canEdit()): ?>
    <section class="danger-zone">
        <form method="post" action="/hymns/<?php echo (int) $hymn['id']; ?>/hide"><?php echo Csrf::input(); ?><button class="btn" type="submit">隐藏圣诗</button></form>
        <form method="post" action="/hymns/<?php echo (int) $hymn['id']; ?>/delete"><?php echo Csrf::input(); ?><button class="btn danger" type="submit">归档删除</button></form>
    </section>
<?php endif; ?>

