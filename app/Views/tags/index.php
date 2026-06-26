<?php use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">标签体系</p>
        <h1>细粒度主题标签</h1>
    </div>
</div>

<section class="two-col">
    <form method="post" action="/tags/groups" class="card form-grid">
        <?php echo Csrf::input(); ?>
        <h2>新增标签组</h2>
        <label>名称<input name="name" required></label>
        <label>编码<input name="code" placeholder="worship_slot" required></label>
        <label>排序<input type="number" name="sort_order" value="0"></label>
        <label>说明<textarea name="description" rows="3"></textarea></label>
        <button class="btn primary" type="submit">创建标签组</button>
    </form>

    <form method="post" action="/tags" class="card form-grid">
        <?php echo Csrf::input(); ?>
        <h2>新增标签</h2>
        <label>所属分组
            <select name="group_id" required>
                <?php foreach ($tagGroups as $group): ?><option value="<?php echo (int) $group['id']; ?>"><?php echo e($group['name']); ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>名称<input name="name" required></label>
        <label>编码<input name="code" required></label>
        <label>排序<input type="number" name="sort_order" value="0"></label>
        <label>说明<textarea name="description" rows="3"></textarea></label>
        <button class="btn primary" type="submit">创建标签</button>
    </form>
</section>

<section class="tag-groups">
    <?php foreach ($tagGroups as $group): ?>
        <article class="card tag-group">
            <div class="section-head">
                <div>
                    <h2><?php echo e($group['name']); ?></h2>
                    <p class="muted"><?php echo e($group['code']); ?>｜<?php echo e($group['description']); ?></p>
                </div>
                <span class="muted"><?php echo count($group['tags']); ?> 个标签</span>
            </div>
            <div class="tag-cloud">
                <?php foreach ($group['tags'] as $tag): ?><span class="tag-chip"><?php echo e($tag['name']); ?></span><?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

