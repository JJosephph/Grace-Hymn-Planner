<?php

function e($value): string
{
    if ($value === null) {
        return '';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function nl2br_e($value): string
{
    return nl2br(e($value));
}

