<?php use App\Core\Auth; use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">圣诗资料库</p>
        <h1>搜索、筛选、加入本周候选</h1>
    </div>
    <?php if (Auth::canEdit()): ?><a class="btn primary" href="/hymns/create">新增圣诗</a><?php endif; ?>
</div>

<div class="library-layout">
    <aside class="filter-panel card">
        <form method="get" action="/hymns">
            <label>搜索<input name="q" value="<?php echo e($filters['q']); ?>" placeholder="标题、歌词、经文、曲调"></label>
            <label>完整度
                <select name="completeness_status">
                    <option value="">全部</option>
                    <?php foreach (['draft' => '草稿', 'incomplete' => '待补全', 'usable' => '基本可用', 'complete' => '完整'] as $value => $label): ?>
                        <option value="<?php echo e($value); ?>" <?php echo $filters['completeness_status'] === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>缺失项
                <select name="missing_field">
                    <option value="">不限</option>
                    <?php foreach (['lyrics' => '缺歌词', 'score_files' => '缺歌谱', 'tags' => '缺标签', 'scripture_refs' => '缺经文', 'worship_slot' => '缺崇拜环节', 'doctrine_tags' => '缺神学主题'] as $value => $label): ?>
                        <option value="<?php echo e($value); ?>" <?php echo $filters['missing_field'] === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>曲调
                <select name="tune_id">
                    <option value="">全部曲调</option>
                    <?php foreach ($tunes as $tune): ?>
                        <option value="<?php echo (int) $tune['id']; ?>" <?php echo (string) $filters['tune_id'] === (string) $tune['id'] ? 'selected' : ''; ?>><?php echo e($tune['tune_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="filter-tags">
                <?php $selectedFilterTags = array_map('intval', (array) $filters['tag_ids']); ?>
                <?php foreach ($tagGroups as $group): ?>
                    <details>
                        <summary><?php echo e($group['name']); ?></summary>
                        <?php foreach ($group['tags'] as $tag): ?>
                            <label class="check-chip">
                                <input type="checkbox" name="tag_ids[]" value="<?php echo (int) $tag['id']; ?>" <?php echo in_array((int) $tag['id'], $selectedFilterTags, true) ? 'checked' : ''; ?>>
                                <span><?php echo e($tag['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </details>
                <?php endforeach; ?>
            </div>
            <button class="btn primary full" type="submit">应用筛选</button>
            <a class="btn ghost full" href="/hymns">清空</a>
        </form>
    </aside>

    <section class="hymn-results">
        <?php foreach ($hymns as $hymn): ?>
            <article class="hymn-card card" data-drawer-title="<?php echo e($hymn['title_cn']); ?>" data-drawer-body="<?php echo e($hymn['first_line'] ?: $hymn['scripture_refs']); ?>" data-drawer-url="/hymns/<?php echo (int) $hymn['id']; ?>">
                <div class="hymn-card-main">
                    <div>
                        <h2><a href="/hymns/<?php echo (int) $hymn['id']; ?>"><?php echo e($hymn['title_cn']); ?></a></h2>
                        <p class="muted"><?php echo e($hymn['title_en']); ?></p>
                    </div>
                    <?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?>
                </div>
                <p class="first-line"><?php echo e($hymn['first_line'] ?: '尚未填写第一句歌词'); ?></p>
                <div class="meta-row">
                    <?php if ($hymn['tune_name']): ?><span>曲调：<?php echo e($hymn['tune_name']); ?></span><?php endif; ?>
                    <span>熟悉度 <?php echo (int) $hymn['familiarity']; ?></span>
                    <span>难度 <?php echo (int) $hymn['difficulty']; ?></span>
                </div>
                <?php $missingFields = $hymn['missing_fields']; require BASE_PATH . '/app/Views/partials/missing_fields.php'; ?>
                <div class="tag-cloud small">
                    <?php foreach (array_filter(explode(' / ', $hymn['tag_names'] ?? '')) as $tagName): ?>
                        <span class="tag-chip"><?php echo e($tagName); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="card-actions">
                    <a class="btn" href="/hymns/<?php echo (int) $hymn['id']; ?>">查看</a>
                    <?php if (Auth::canEdit()): ?><a class="btn" href="/hymns/<?php echo (int) $hymn['id']; ?>/edit">编辑</a><?php endif; ?>
                    <button class="btn ghost" type="button" data-copy="<?php echo e($hymn['title_cn']); ?>">复制标题</button>
                    <?php if ($latestPlan): ?>
                        <form method="post" action="/plans/<?php echo (int) $latestPlan['id']; ?>/items">
                            <?php echo Csrf::input(); ?>
                            <input type="hidden" name="hymn_id" value="<?php echo (int) $hymn['id']; ?>">
                            <input type="hidden" name="slot_type" value="candidate">
                            <input type="hidden" name="item_status" value="candidate">
                            <button class="btn primary" type="submit">加入本周</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$hymns): ?><div class="empty-state card">没有找到匹配的圣诗。</div><?php endif; ?>
    </section>

    <aside class="right-drawer card" data-right-drawer>
        <p class="eyebrow">详情预览</p>
        <h2 data-drawer-heading>点击诗歌卡片</h2>
        <p class="muted" data-drawer-text>先在右侧查看摘要，再决定是否打开详情。</p>
        <a class="btn primary" data-drawer-link href="/hymns">查看详情</a>
    </aside>
</div>

