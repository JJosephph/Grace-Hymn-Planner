<?php

namespace App\Models;

use App\Core\Model;

class File extends Model
{
    public function findHymnFile(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM hymn_files WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        return $file ?: null;
    }

    public function createHymnFile(int $hymnId, array $file): int
    {
        $stmt = $this->db->prepare('INSERT INTO hymn_files (hymn_id, file_type, file_name, original_name, file_path, file_size, mime_type, is_cover, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sortOrder = $this->nextSortOrder($hymnId);
        $stmt->execute([
            $hymnId,
            $file['file_type'],
            $file['file_name'],
            $file['original_name'],
            $file['file_path'],
            $file['file_size'],
            $file['mime_type'],
            (int) ($file['is_cover'] ?? 0),
            $sortOrder,
            $this->now(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteHymnFile(int $id): ?array
    {
        $file = $this->findHymnFile($id);
        if (!$file) {
            return null;
        }

        $stmt = $this->db->prepare('DELETE FROM hymn_files WHERE id = ?');
        $stmt->execute([$id]);
        return $file;
    }

    private function nextSortOrder(int $hymnId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order), 0) + 10 AS next_value FROM hymn_files WHERE hymn_id = ?');
        $stmt->execute([$hymnId]);
        $row = $stmt->fetch();
        return (int) ($row['next_value'] ?? 10);
    }
}

