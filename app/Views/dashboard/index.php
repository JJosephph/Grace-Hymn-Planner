<div class="page-head">
    <div>
        <p class="eyebrow">司会工作台</p>
        <h1>为本周崇拜安静地选好诗歌</h1>
    </div>
    <div class="head-actions">
        <a class="btn primary" href="/plans/create">新建本周计划</a>
        <a class="btn" href="/hymns">搜索圣诗</a>
    </div>
</div>

<section class="bento-grid">
    <article class="bento-card wide">
        <p class="eyebrow">本周崇拜计划</p>
        <?php if ($latestPlan): ?>
            <h2><?php echo e($latestPlan['title']); ?></h2>
            <p class="muted"><?php echo e($latestPlan['service_date']); ?>｜<?php echo e($latestPlan['sermon_title'] ?: '未填写证道题目'); ?></p>
            <p><?php echo e($latestPlan['sermon_scripture'] ?: '尚未填写证道经文'); ?></p>
            <a class="btn primary" href="/plans/<?php echo (int) $latestPlan['id']; ?>">进入选诗</a>
        <?php else: ?>
            <h2>还没有崇拜计划</h2>
            <p class="muted">先建立本周计划，再把候选诗歌放进四个主要环节。</p>
            <a class="btn primary" href="/plans/create">建立计划</a>
        <?php endif; ?>
    </article>

    <article class="bento-card">
        <p class="eyebrow">圣诗资料库</p>
        <h2><?php echo (int) $counts['total']; ?></h2>
        <p class="muted">已录入圣诗</p>
    </article>
    <article class="bento-card">
        <p class="eyebrow">待补全</p>
        <h2><?php echo (int) $counts['incomplete_total']; ?></h2>
        <p class="muted">缺歌词、歌谱、标签或经文</p>
    </article>
</section>

<section class="two-col">
    <div class="card">
        <div class="section-head">
            <h2>最近新增圣诗</h2>
            <a href="/hymns/create">新增</a>
        </div>
        <div class="compact-list">
            <?php foreach ($latestHymns as $hymn): ?>
                <a href="/hymns/<?php echo (int) $hymn['id']; ?>">
                    <strong><?php echo e($hymn['title_cn']); ?></strong>
                    <span><?php echo e($hymn['first_line']); ?></span>
                    <?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$latestHymns): ?><div class="empty-state">还没有录入圣诗。</div><?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="section-head">
            <h2>待补全圣诗</h2>
            <a href="/hymns?completeness_status=incomplete">查看</a>
        </div>
        <div class="compact-list">
            <?php foreach ($incompleteHymns as $hymn): ?>
                <a href="/hymns/<?php echo (int) $hymn['id']; ?>/edit">
                    <strong><?php echo e($hymn['title_cn']); ?></strong>
                    <?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?>
                    <?php $missingFields = $hymn['missing_fields']; require BASE_PATH . '/app/Views/partials/missing_fields.php'; ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$incompleteHymns): ?><div class="empty-state">资料都很整齐。</div><?php endif; ?>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-head">
        <h2>常用标签入口</h2>
        <a href="/tags">管理标签</a>
    </div>
    <div class="tag-cloud">
        <?php foreach (array_slice($tagGroups, 0, 5) as $group): ?>
            <?php foreach (array_slice($group['tags'], 0, 6) as $tag): ?>
                <a class="tag-chip" href="/hymns?tag_ids[]=<?php echo (int) $tag['id']; ?>"><?php echo e($tag['name']); ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</section>

