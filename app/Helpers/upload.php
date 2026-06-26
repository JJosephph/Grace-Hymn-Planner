<?php

function allowedUploadExtensions(): array
{
    return ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'ppt', 'pptx', 'doc', 'docx', 'mp3', 'm4a'];
}

function forbiddenUploadExtensions(): array
{
    return ['php', 'phtml', 'js', 'html', 'exe', 'sh', 'bat'];
}

function classifyUploadType(string $extension): string
{
    $extension = strtolower($extension);

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return 'score_image';
    }
    if ($extension === 'pdf') {
        return 'score_pdf';
    }
    if (in_array($extension, ['ppt', 'pptx'], true)) {
        return 'ppt';
    }
    if (in_array($extension, ['doc', 'docx'], true)) {
        return 'doc';
    }
    if (in_array($extension, ['mp3', 'm4a'], true)) {
        return 'audio';
    }

    return 'other';
}

function storeUploadedFile(array $file, string $targetDir): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('文件上传失败。');
    }

    $originalName = (string) ($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($extension === '' || in_array($extension, forbiddenUploadExtensions(), true) || !in_array($extension, allowedUploadExtensions(), true)) {
        throw new RuntimeException('不支持的文件格式。');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('上传文件无效。');
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $fileName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetPath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('无法保存上传文件。');
    }

    return [
        'file_name' => $fileName,
        'original_name' => $originalName,
        'file_size' => (int) ($file['size'] ?? 0),
        'mime_type' => (string) ($file['type'] ?? ''),
        'extension' => $extension,
        'absolute_path' => $targetPath,
    ];
}

