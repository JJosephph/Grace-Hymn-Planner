<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\File;
use App\Models\Hymn;

class FileController extends Controller
{
    public function uploadHymnFile(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        $hymnId = (int) $id;

        try {
            if (empty($_FILES['attachment']['name'])) {
                throw new \RuntimeException('请选择要上传的文件。');
            }

            $targetDir = BASE_PATH . '/public/uploads/hymns/' . $hymnId;
            $stored = storeUploadedFile($_FILES['attachment'], $targetDir);
            $relative = '/uploads/hymns/' . $hymnId . '/' . $stored['file_name'];
            (new File())->createHymnFile($hymnId, [
                'file_type' => classifyUploadType($stored['extension']),
                'file_name' => $stored['file_name'],
                'original_name' => $stored['original_name'],
                'file_path' => $relative,
                'file_size' => $stored['file_size'],
                'mime_type' => $stored['mime_type'],
                'is_cover' => isset($_POST['is_cover']) ? 1 : 0,
            ]);
            (new Hymn())->refreshCompleteness($hymnId);
            set_flash('success', '附件已上传。');
        } catch (\Throwable $exception) {
            set_flash('error', $exception->getMessage());
        }

        $this->redirect('/hymns/' . $hymnId . '/edit');
    }

    public function downloadHymnFile(string $id): void
    {
        Auth::requireLogin();
        $file = (new File())->findHymnFile((int) $id);
        if (!$file) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        $path = BASE_PATH . '/public' . $file['file_path'];
        if (!is_file($path)) {
            http_response_code(404);
            echo 'File missing';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($file['original_name']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function deleteHymnFile(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        $fileModel = new File();
        $file = $fileModel->deleteHymnFile((int) $id);
        if ($file) {
            $path = BASE_PATH . '/public' . $file['file_path'];
            if (is_file($path)) {
                unlink($path);
            }
            (new Hymn())->refreshCompleteness((int) $file['hymn_id']);
            set_flash('success', '附件已删除。');
            $this->redirect('/hymns/' . (int) $file['hymn_id'] . '/edit');
        }
        $this->redirect('/hymns');
    }
}

