<?php $missingLabels = missingFieldLabels($missingFields ?? ($hymn['missing_fields'] ?? null)); ?>
<?php if ($missingLabels): ?>
    <div class="missing-list">
        <?php foreach ($missingLabels as $label): ?>
            <span><?php echo e($label); ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

