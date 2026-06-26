<?php use App\Core\Csrf; ?>
<?php
$action = $hymn ? '/hymns/' . (int) $hymn['id'] : '/hymns';
$selectedTags = $selectedTags ?? [];
?>
<form method="post" action="<?php echo e($action); ?>" class="hymn-form">
    <?php echo Csrf::input(); ?>
    <section class="card">
        <div class="section-head">
            <div>
                <p class="eyebrow">快速录入</p>
                <h2>先保存标题，后续慢慢补全</h2>
            </div>
            <?php if ($hymn): ?><?php require BASE_PATH . '/app/Views/partials/completeness.php'; ?><?php endif; ?>
        </div>
        <div class="form-grid three">
            <label>中文诗歌名 *<input name="title_cn" value="<?php echo e($hymn['title_cn'] ?? ''); ?>" required></label>
            <label>第一句歌词<input name="first_line" value="<?php echo e($hymn['first_line'] ?? ''); ?>"></label>
            <label>英文名<input name="title_en" value="<?php echo e($hymn['title_en'] ?? ''); ?>"></label>
        </div>
        <?php if ($hymn): ?><?php $missingFields = $hymn['missing_fields']; require BASE_PATH . '/app/Views/partials/missing_fields.php'; ?><?php endif; ?>
    </section>

    <section class="card">
        <div class="section-head"><h2>基本信息</h2><span class="muted">曲调、来源、作者与使用难度</span></div>
        <div class="form-grid three">
            <label>曲调
                <select name="tune_id">
                    <option value="">未关联曲调</option>
                    <?php foreach ($tunes as $tune): ?>
                        <option value="<?php echo (int) $tune['id']; ?>" <?php echo (string) ($hymn['tune_id'] ?? '') === (string) $tune['id'] ? 'selected' : ''; ?>><?php echo e($tune['tune_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>别名<textarea name="alias" rows="2"><?php echo e($hymn['alias'] ?? ''); ?></textarea></label>
            <label>来源歌本<input name="source_book" value="<?php echo e($hymn['source_book'] ?? ''); ?>"></label>
            <label>歌本编号<input name="hymn_number" value="<?php echo e($hymn['hymn_number'] ?? ''); ?>"></label>
            <label>词作者<input name="author" value="<?php echo e($hymn['author'] ?? ''); ?>"></label>
            <label>曲作者<input name="composer" value="<?php echo e($hymn['composer'] ?? ''); ?>"></label>
            <label>译者<input name="translator" value="<?php echo e($hymn['translator'] ?? ''); ?>"></label>
            <label>调号<input name="key_signature" value="<?php echo e($hymn['key_signature'] ?? ''); ?>"></label>
            <label>韵律<input name="meter" value="<?php echo e($hymn['meter'] ?? ''); ?>"></label>
            <label>速度/情绪<input name="tempo" value="<?php echo e($hymn['tempo'] ?? ''); ?>"></label>
            <label>演唱难度
                <select name="difficulty">
                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?php echo $i; ?>" <?php echo (int) ($hymn['difficulty'] ?? 3) === $i ? 'selected' : ''; ?>><?php echo $i; ?></option><?php endfor; ?>
                </select>
            </label>
            <label>会众熟悉度
                <select name="familiarity">
                    <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?php echo $i; ?>" <?php echo (int) ($hymn['familiarity'] ?? 3) === $i ? 'selected' : ''; ?>><?php echo $i; ?></option><?php endfor; ?>
                </select>
            </label>
            <label>状态
                <select name="status">
                    <?php foreach (['active' => '正常', 'hidden' => '隐藏', 'archived' => '归档'] as $value => $label): ?>
                        <option value="<?php echo e($value); ?>" <?php echo ($hymn['status'] ?? 'active') === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </section>

    <section class="card">
        <div class="section-head"><h2>歌词内容</h2><span class="muted">完整歌词和 PPT 简版歌词可分别复制</span></div>
        <div class="form-grid two">
            <label>完整歌词<textarea class="lyrics-input" name="lyrics" rows="12"><?php echo e($hymn['lyrics'] ?? ''); ?></textarea></label>
            <label>PPT 简版歌词<textarea class="lyrics-input" name="ppt_lyrics" rows="12"><?php echo e($hymn['ppt_lyrics'] ?? ''); ?></textarea></label>
        </div>
    </section>

    <section class="card">
        <div class="section-head"><h2>神学与经文</h2><span class="muted">帮助司会判断诗歌与证道的贴合度</span></div>
        <div class="form-grid two">
            <label>相关经文<input name="scripture_refs" value="<?php echo e($hymn['scripture_refs'] ?? ''); ?>"></label>
            <label>版权状态
                <select name="license_status">
                    <?php foreach (['unknown' => '未知', 'public_domain' => '公版', 'church_internal' => '教会内部', 'licensed' => '已授权'] as $value => $label): ?>
                        <option value="<?php echo e($value); ?>" <?php echo ($hymn['license_status'] ?? 'unknown') === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>神学摘要<textarea name="doctrine_summary" rows="5"><?php echo e($hymn['doctrine_summary'] ?? ''); ?></textarea></label>
            <label>使用建议<textarea name="usage_note" rows="5"><?php echo e($hymn['usage_note'] ?? ''); ?></textarea></label>
            <label>版权说明<textarea name="copyright_note" rows="3"><?php echo e($hymn['copyright_note'] ?? ''); ?></textarea></label>
        </div>
    </section>

    <section class="card">
        <div class="section-head"><h2>标签分类</h2><span class="muted">多标签默认按 AND 组合筛选</span></div>
        <div class="tag-groups">
            <?php foreach ($tagGroups as $group): ?>
                <div class="tag-group">
                    <h3><?php echo e($group['name']); ?></h3>
                    <div class="tag-cloud">
                        <?php foreach ($group['tags'] as $tag): ?>
                            <label class="check-chip">
                                <input type="checkbox" name="tag_ids[]" value="<?php echo (int) $tag['id']; ?>" <?php echo in_array((int) $tag['id'], $selectedTags, true) ? 'checked' : ''; ?>>
                                <span><?php echo e($tag['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="sticky-actions">
        <?php if (!$hymn): ?>
            <button class="btn" name="next" value="draft" type="submit">保存草稿</button>
            <button class="btn primary" name="next" value="continue" type="submit">保存并继续完善</button>
        <?php else: ?>
            <a class="btn" href="/hymns/<?php echo (int) $hymn['id']; ?>">查看详情</a>
            <button class="btn primary" type="submit">保存修改</button>
        <?php endif; ?>
    </div>
</form>

<?php if ($hymn): ?>
    <section class="card">
        <div class="section-head"><h2>附件上传</h2><span class="muted">支持歌谱图片、PDF、PPT、Word、音频</span></div>
        <form class="upload-dropzone" method="post" action="/hymns/<?php echo (int) $hymn['id']; ?>/files" enctype="multipart/form-data">
            <?php echo Csrf::input(); ?>
            <input type="file" name="attachment" required>
            <label class="inline-check"><input type="checkbox" name="is_cover" value="1"> 设为封面歌谱</label>
            <button class="btn primary" type="submit">上传附件</button>
        </form>
        <div class="file-grid">
            <?php foreach ($hymn['files'] as $file): ?>
                <article class="file-item">
                    <?php if ($file['file_type'] === 'score_image'): ?>
                        <img src="<?php echo e(public_file_url($file['file_path'])); ?>" alt="<?php echo e($file['original_name']); ?>">
                    <?php elseif ($file['file_type'] === 'score_pdf'): ?>
                        <embed src="<?php echo e(public_file_url($file['file_path'])); ?>" type="application/pdf">
                    <?php endif; ?>
                    <strong><?php echo e($file['original_name']); ?></strong>
                    <span><?php echo e($file['file_type']); ?></span>
                    <div class="card-actions">
                        <a class="btn" href="/files/<?php echo (int) $file['id']; ?>/download">下载</a>
                        <form method="post" action="/files/<?php echo (int) $file['id']; ?>/delete">
                            <?php echo Csrf::input(); ?>
                            <button class="btn danger" type="submit">删除</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
