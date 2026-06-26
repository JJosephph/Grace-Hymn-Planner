<?php use App\Core\Csrf; ?>
<div class="page-head">
    <div>
        <p class="eyebrow">曲调管理</p>
        <h1><?php echo e($tune ? '编辑曲调' : '新增曲调'); ?></h1>
    </div>
</div>

<form method="post" action="<?php echo $tune ? '/tunes/' . (int) $tune['id'] : '/tunes'; ?>" class="card form-grid two">
    <?php echo Csrf::input(); ?>
    <label>曲调名 *<input name="tune_name" value="<?php echo e($tune['tune_name'] ?? ''); ?>" required></label>
    <label>英文名 / 别名<input name="tune_name_en" value="<?php echo e($tune['tune_name_en'] ?? ''); ?>"></label>
    <label>曲作者<input name="composer" value="<?php echo e($tune['composer'] ?? ''); ?>"></label>
    <label>韵律<input name="meter" value="<?php echo e($tune['meter'] ?? ''); ?>"></label>
    <label>常用调号<input name="key_signature" value="<?php echo e($tune['key_signature'] ?? ''); ?>"></label>
    <label>速度 / 情绪<input name="tempo" value="<?php echo e($tune['tempo'] ?? ''); ?>"></label>
    <label class="span-2">备注<textarea name="note" rows="6"><?php echo e($tune['note'] ?? ''); ?></textarea></label>
    <div class="span-2 sticky-actions inline">
        <a class="btn" href="/tunes">返回</a>
        <button class="btn primary" type="submit">保存曲调</button>
    </div>
</form>

