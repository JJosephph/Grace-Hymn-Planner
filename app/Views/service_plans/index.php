<div class="page-head">
    <div>
        <p class="eyebrow">本周崇拜选诗</p>
        <h1>计划、候选、确认与导出</h1>
    </div>
    <a class="btn primary" href="/plans/create">新建计划</a>
</div>

<section class="card-grid">
    <?php foreach ($plans as $plan): ?>
        <article class="card">
            <p class="eyebrow"><?php echo e($plan['service_date']); ?></p>
            <h2><a href="/plans/<?php echo (int) $plan['id']; ?>"><?php echo e($plan['title']); ?></a></h2>
            <p class="muted"><?php echo e($plan['sermon_title'] ?: '证道题目待填写'); ?></p>
            <p><?php echo e($plan['sermon_scripture']); ?></p>
            <div class="meta-row"><span><?php echo (int) $plan['item_count']; ?> 首候选/选用诗歌</span></div>
            <a class="btn primary" href="/plans/<?php echo (int) $plan['id']; ?>">进入工作台</a>
        </article>
    <?php endforeach; ?>
    <?php if (!$plans): ?><div class="empty-state card">还没有崇拜计划。</div><?php endif; ?>
</section>

