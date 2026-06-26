<?php
$status = $status ?? ($hymn['completeness_status'] ?? 'draft');
$score = $score ?? ($hymn['completeness_score'] ?? 0);
?>
<span class="completeness-badge <?php echo e($status); ?>">
    <?php echo e(completenessLabel($status)); ?> <?php echo (int) $score; ?>%
</span>

