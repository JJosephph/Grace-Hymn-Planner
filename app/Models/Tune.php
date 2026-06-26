<?php

namespace App\Models;

use App\Core\Model;

class Tune extends Model
{
    public function all(): array
    {
        $sql = 'SELECT t.*, COALESCE(hc.hymn_count, 0) AS hymn_count
                FROM tunes t
                LEFT JOIN (
                    SELECT tune_id, COUNT(*) AS hymn_count
                    FROM hymns
                    WHERE status <> "archived" AND tune_id IS NOT NULL
                    GROUP BY tune_id
                ) hc ON hc.tune_id = t.id
                ORDER BY t.updated_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function options(): array
    {
        return $this->db->query('SELECT id, tune_name, tune_name_en FROM tunes ORDER BY tune_name ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tunes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $tune = $stmt->fetch();
        if (!$tune) {
            return null;
        }

        $hymnsStmt = $this->db->prepare('SELECT id, title_cn, title_en, first_line, completeness_status, completeness_score FROM hymns WHERE tune_id = ? AND status <> "archived" ORDER BY title_cn ASC');
        $hymnsStmt->execute([$id]);
        $tune['hymns'] = $hymnsStmt->fetchAll();

        $filesStmt = $this->db->prepare('SELECT * FROM tune_files WHERE tune_id = ? ORDER BY sort_order ASC, id ASC');
        $filesStmt->execute([$id]);
        $tune['files'] = $filesStmt->fetchAll();

        return $tune;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO tunes (tune_name, tune_name_en, composer, meter, key_signature, tempo, note, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $now = $this->now();
        $stmt->execute([
            trim($data['tune_name'] ?? ''),
            trim($data['tune_name_en'] ?? ''),
            trim($data['composer'] ?? ''),
            trim($data['meter'] ?? ''),
            trim($data['key_signature'] ?? ''),
            trim($data['tempo'] ?? ''),
            trim($data['note'] ?? ''),
            $now,
            $now,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE tunes SET tune_name = ?, tune_name_en = ?, composer = ?, meter = ?, key_signature = ?, tempo = ?, note = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([
            trim($data['tune_name'] ?? ''),
            trim($data['tune_name_en'] ?? ''),
            trim($data['composer'] ?? ''),
            trim($data['meter'] ?? ''),
            trim($data['key_signature'] ?? ''),
            trim($data['tempo'] ?? ''),
            trim($data['note'] ?? ''),
            $this->now(),
            $id,
        ]);
    }
}
