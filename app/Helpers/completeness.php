<?php

function hasTagGroup(array $tags, string $groupCode): bool
{
    foreach ($tags as $tag) {
        if (($tag['group_code'] ?? '') === $groupCode) {
            return true;
        }
    }

    return false;
}

function hasAnyTagGroup(array $tags, array $groupCodes): bool
{
    foreach ($groupCodes as $groupCode) {
        if (hasTagGroup($tags, $groupCode)) {
            return true;
        }
    }

    return false;
}

function calculateHymnCompleteness(array $hymn, array $tags, array $files): array
{
    $score = 0;
    $missing = [];

    if (!empty($hymn['title_cn'])) {
        $score += 20;
    }

    if (!empty(trim((string) ($hymn['lyrics'] ?? '')))) {
        $score += 25;
    } else {
        $missing[] = 'lyrics';
    }

    if (!empty($tags)) {
        $score += 15;
    } else {
        $missing[] = 'tags';
    }

    if (hasTagGroup($tags, 'worship_slot')) {
        $score += 10;
    } else {
        $missing[] = 'worship_slot';
    }

    if (hasAnyTagGroup($tags, ['christology', 'soteriology', 'sanctification_response', 'trinity_object'])) {
        $score += 10;
    } else {
        $missing[] = 'doctrine_tags';
    }

    if (!empty(trim((string) ($hymn['scripture_refs'] ?? '')))) {
        $score += 10;
    } else {
        $missing[] = 'scripture_refs';
    }

    if (!empty($files)) {
        $score += 10;
    } else {
        $missing[] = 'score_files';
    }

    if ($score < 30) {
        $status = 'draft';
    } elseif ($score < 70) {
        $status = 'incomplete';
    } elseif ($score < 90) {
        $status = 'usable';
    } else {
        $status = 'complete';
    }

    return [
        'score' => $score,
        'status' => $status,
        'missing' => $missing,
    ];
}

function missingFieldLabels(?string $missingFields): array
{
    $labels = [
        'lyrics' => '缺歌词',
        'tags' => '缺标签',
        'worship_slot' => '缺崇拜环节',
        'doctrine_tags' => '缺神学主题',
        'scripture_refs' => '缺相关经文',
        'score_files' => '缺歌谱附件',
    ];

    if (!$missingFields) {
        return [];
    }

    $result = [];
    foreach (explode(',', $missingFields) as $field) {
        $field = trim($field);
        if ($field !== '') {
            $result[] = $labels[$field] ?? $field;
        }
    }

    return $result;
}

function completenessLabel(string $status): string
{
    $labels = [
        'draft' => '草稿',
        'incomplete' => '待补全',
        'usable' => '基本可用',
        'complete' => '完整',
    ];

    return $labels[$status] ?? $status;
}

