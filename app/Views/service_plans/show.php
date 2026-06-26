<?php use App\Core\Auth; use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">本周选诗工作台</p>
        <h1><?php echo e($plan['title']); ?></h1>
        <p class="muted"><?php echo e($plan['service_date']); ?>｜<?php echo e($plan['sermon_title']); ?>｜<?php echo e($plan['sermon_scripture']); ?></p>
    </div>
    <div class="head-actions">
        <a class="btn" href="/plans/<?php echo (int) $plan['id']; ?>/export">导出清单</a>
        <a class="btn primary" href="/hymns">去圣诗库筛选</a>
    </div>
</div>

<section class="card">
    <form method="post" action="/plans/<?php echo (int) $plan['id']; ?>" class="form-grid two">
        <?php echo Csrf::input(); ?>
        <label>计划标题<input name="title" value="<?php echo e($plan['title']); ?>" required></label>
        <label>崇拜日期<input type="date" name="service_date" value="<?php echo e($plan['service_date']); ?>" required></label>
        <label>证道题目<input name="sermon_title" value="<?php echo e($plan['sermon_title']); ?>"></label>
        <label>证道经文<input name="sermon_scripture" value="<?php echo e($plan['sermon_scripture']); ?>"></label>
        <label class="span-2">主题句<textarea name="sermon_theme" rows="2"><?php echo e($plan['sermon_theme']); ?></textarea></label>
        <label class="span-2">讲道大纲<textarea name="sermon_outline" rows="4"><?php echo e($plan['sermon_outline']); ?></textarea></label>
        <label>关键词<input name="sermon_keywords" value="<?php echo e($plan['sermon_keywords']); ?>"></label>
        <label>备注<input name="notes" value="<?php echo e($plan['notes']); ?>"></label>
        <?php if (Auth::canEdit()): ?><button class="btn primary span-2" type="submit">保存证道信息</button><?php endif; ?>
    </form>
</section>

<div class="plan-layout">
    <section class="slot-grid">
        <?php foreach ($slotOptions as $slot => $label): ?>
            <?php if ($slot === 'candidate') { continue; } ?>
            <article class="card slot-card">
                <h2><?php echo e($label); ?></h2>
                <?php foreach ($plan['items_grouped'][$slot] ?? [] as $item): ?>
                    <div class="plan-item">
                        <strong><a href="/hymns/<?php echo (int) $item['hymn_id']; ?>"><?php echo e($item['title_cn']); ?></a></strong>
                        <span class="muted"><?php echo e($item['first_line']); ?></span>
                        <?php if (Auth::canEdit()): ?>
                            <form method="post" action="/plans/items/<?php echo (int) $item['id']; ?>">
                                <?php echo Csrf::input(); ?>
                                <input type="hidden" name="item_status" value="selected">
                                <select name="slot_type">
                                    <?php foreach ($slotOptions as $slotValue => $slotLabel): ?><option value="<?php echo e($slotValue); ?>" <?php echo $item['slot_type'] === $slotValue ? 'selected' : ''; ?>><?php echo e($slotLabel); ?></option><?php endforeach; ?>
                                </select>
                                <input name="note" value="<?php echo e($item['note']); ?>" placeholder="备注">
                                <button class="btn" type="submit">更新</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($plan['items_grouped'][$slot])): ?><div class="empty-state">尚未分配诗歌。</div><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>

    <aside class="card candidate-pool">
        <div class="section-head"><h2>候选诗歌池</h2><span><?php echo count($plan['items_grouped']['candidate'] ?? []); ?> 首</span></div>
        <?php foreach ($plan['items_grouped']['candidate'] ?? [] as $item): ?>
            <div class="candidate-item">
                <strong><?php echo e($item['title_cn']); ?></strong>
                <span class="muted"><?php echo e($item['first_line']); ?></span>
                <?php if (Auth::canEdit()): ?>
                    <form method="post" action="/plans/items/<?php echo (int) $item['id']; ?>">
                        <?php echo Csrf::input(); ?>
                        <input type="hidden" name="item_status" value="selected">
                        <select name="slot_type">
                            <?php foreach ($slotOptions as $slotValue => $slotLabel): ?>
                                <?php if ($slotValue !== 'candidate'): ?><option value="<?php echo e($slotValue); ?>"><?php echo e($slotLabel); ?></option><?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <input name="note" value="<?php echo e($item['note']); ?>" placeholder="适合原因">
                        <button class="btn primary" type="submit">分配</button>
                    </form>
                    <form method="post" action="/plans/items/<?php echo (int) $item['id']; ?>/delete">
                        <?php echo Csrf::input(); ?>
                        <button class="btn ghost" type="submit">移除</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($plan['items_grouped']['candidate'])): ?><div class="empty-state">候选池为空，可以从圣诗库加入。</div><?php endif; ?>
    </aside>
</div>

<section class="card">
    <div class="section-head"><h2>快速加入诗歌</h2><span class="muted">从已录入圣诗里直接加入候选池</span></div>
    <form method="post" action="/plans/<?php echo (int) $plan['id']; ?>/items" class="form-grid three">
        <?php echo Csrf::input(); ?>
        <label>圣诗
            <select name="hymn_id">
                <?php foreach ($hymns as $hymn): ?><option value="<?php echo (int) $hymn['id']; ?>"><?php echo e($hymn['title_cn']); ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>环节
            <select name="slot_type">
                <?php foreach ($slotOptions as $slotValue => $slotLabel): ?><option value="<?php echo e($slotValue); ?>"><?php echo e($slotLabel); ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>状态
            <select name="item_status"><option value="candidate">候选</option><option value="selected">已选</option></select>
        </label>
        <label class="span-2">备注<input name="note" placeholder="适合原因、司琴提醒"></label>
        <button class="btn primary" type="submit">加入计划</button>
    </form>
</section>

