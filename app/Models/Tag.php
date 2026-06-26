<?php

namespace App\Models;

use App\Core\Model;

class Tag extends Model
{
    public function allGroupsWithTags(): array
    {
        $groups = $this->db->query('SELECT * FROM tag_groups ORDER BY sort_order ASC, id ASC')->fetchAll();
        $stmt = $this->db->query('SELECT t.*, g.code AS group_code, g.name AS group_name FROM tags t INNER JOIN tag_groups g ON g.id = t.group_id ORDER BY g.sort_order ASC, t.sort_order ASC, t.id ASC');
        $tags = $stmt->fetchAll();

        $byGroup = [];
        foreach ($tags as $tag) {
            $byGroup[$tag['group_id']][] = $tag;
        }

        foreach ($groups as &$group) {
            $group['tags'] = $byGroup[$group['id']] ?? [];
        }

        return $groups;
    }

    public function allTags(): array
    {
        return $this->db->query('SELECT t.*, g.code AS group_code, g.name AS group_name FROM tags t INNER JOIN tag_groups g ON g.id = t.group_id ORDER BY g.sort_order ASC, t.sort_order ASC, t.id ASC')->fetchAll();
    }

    public function findByIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT t.*, g.code AS group_code, g.name AS group_name FROM tags t INNER JOIN tag_groups g ON g.id = t.group_id WHERE t.id IN ($placeholders)");
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function createGroup(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO tag_groups (name, code, description, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        $now = $this->now();
        $stmt->execute([
            trim($data['name'] ?? ''),
            trim($data['code'] ?? ''),
            trim($data['description'] ?? ''),
            (int) ($data['sort_order'] ?? 0),
            $now,
            $now,
        ]);
    }

    public function createTag(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO tags (group_id, name, code, description, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $now = $this->now();
        $stmt->execute([
            (int) $data['group_id'],
            trim($data['name'] ?? ''),
            trim($data['code'] ?? ''),
            trim($data['description'] ?? ''),
            (int) ($data['sort_order'] ?? 0),
            $now,
            $now,
        ]);
    }
}

