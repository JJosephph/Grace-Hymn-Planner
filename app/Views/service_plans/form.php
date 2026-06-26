<?php use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">崇拜计划</p>
        <h1>录入证道信息</h1>
    </div>
</div>
<form method="post" action="/plans" class="card form-grid two">
    <?php echo Csrf::input(); ?>
    <label>计划标题<input name="title" value="<?php echo e($plan['title']); ?>" required></label>
    <label>崇拜日期<input type="date" name="service_date" value="<?php echo e($plan['service_date']); ?>" required></label>
    <label>证道题目<input name="sermon_title" value="<?php echo e($plan['sermon_title']); ?>"></label>
    <label>证道经文<input name="sermon_scripture" value="<?php echo e($plan['sermon_scripture']); ?>"></label>
    <label class="span-2">主题句<textarea name="sermon_theme" rows="3"><?php echo e($plan['sermon_theme']); ?></textarea></label>
    <label class="span-2">讲道大纲<textarea name="sermon_outline" rows="6"><?php echo e($plan['sermon_outline']); ?></textarea></label>
    <label>关键词<input name="sermon_keywords" value="<?php echo e($plan['sermon_keywords']); ?>"></label>
    <label>备注<input name="notes" value="<?php echo e($plan['notes']); ?>"></label>
    <button class="btn primary span-2" type="submit">创建并进入选诗</button>
</form>

