<?php

namespace App\Models;

use App\Core\Model;

class ServicePlan extends Model
{
    public static function slotOptions(): array
    {
        return [
            'candidate' => '候选池',
            'opening' => '第一首颂赞诗歌',
            'second' => '第二首颂赞诗歌',
            'before_sermon' => '讲道前诗歌',
            'response' => '证道回应诗歌',
            'communion' => '圣餐诗歌',
            'baptism' => '洗礼诗歌',
            'other' => '其他诗歌',
        ];
    }

    public function all(): array
    {
        $sql = 'SELECT sp.*, COALESCE(pic.item_count, 0) AS item_count
                FROM service_plans sp
                LEFT JOIN (
                    SELECT service_plan_id, COUNT(*) AS item_count
                    FROM service_plan_items
                    WHERE item_status <> "removed"
                    GROUP BY service_plan_id
                ) pic ON pic.service_plan_id = sp.id
                ORDER BY sp.service_date DESC, sp.updated_at DESC';
        return $this->query($sql)->fetchAll();
    }

    public function latest(): ?array
    {
        $stmt = $this->query('SELECT * FROM service_plans ORDER BY service_date DESC, updated_at DESC LIMIT 1');
        $plan = $stmt->fetch();
        return $plan ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->prepare('INSERT INTO service_plans (title, service_date, sermon_title, sermon_scripture, sermon_theme, sermon_outline, sermon_keywords, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $now = $this->now();
        $stmt->execute([
            trim($data['title'] ?? ''),
            trim($data['service_date'] ?? ''),
            trim($data['sermon_title'] ?? ''),
            trim($data['sermon_scripture'] ?? ''),
            trim($data['sermon_theme'] ?? ''),
            trim($data['sermon_outline'] ?? ''),
            trim($data['sermon_keywords'] ?? ''),
            trim($data['notes'] ?? ''),
            $now,
            $now,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePlan(int $id, array $data): void
    {
        $stmt = $this->prepare('UPDATE service_plans SET title = ?, service_date = ?, sermon_title = ?, sermon_scripture = ?, sermon_theme = ?, sermon_outline = ?, sermon_keywords = ?, notes = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([
            trim($data['title'] ?? ''),
            trim($data['service_date'] ?? ''),
            trim($data['sermon_title'] ?? ''),
            trim($data['sermon_scripture'] ?? ''),
            trim($data['sermon_theme'] ?? ''),
            trim($data['sermon_outline'] ?? ''),
            trim($data['sermon_keywords'] ?? ''),
            trim($data['notes'] ?? ''),
            $this->now(),
            $id,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->prepare('SELECT * FROM service_plans WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $plan = $stmt->fetch();
        if (!$plan) {
            return null;
        }

        $itemsStmt = $this->prepare('SELECT spi.*, h.title_cn, h.title_en, h.first_line, h.completeness_status, h.completeness_score, h.scripture_refs, h.usage_note
                                         FROM service_plan_items spi
                                         INNER JOIN hymns h ON h.id = spi.hymn_id
                                         WHERE spi.service_plan_id = ?
                                         ORDER BY spi.sort_order ASC, spi.id ASC');
        $itemsStmt->execute([$id]);
        $plan['items'] = $itemsStmt->fetchAll();

        $grouped = [];
        foreach ($plan['items'] as $item) {
            $grouped[$item['slot_type']][] = $item;
        }
        $plan['items_grouped'] = $grouped;

        return $plan;
    }

    public function addItem(int $planId, int $hymnId, string $slotType = 'candidate', string $status = 'candidate', string $note = ''): void
    {
        $check = $this->prepare('SELECT id FROM service_plan_items WHERE service_plan_id = ? AND hymn_id = ? LIMIT 1');
        $check->execute([$planId, $hymnId]);
        $existing = $check->fetch();
        if ($existing) {
            $this->updateItem((int) $existing['id'], [
                'slot_type' => $slotType,
                'item_status' => $status,
                'note' => $note,
            ]);
            return;
        }

        $stmt = $this->prepare('INSERT INTO service_plan_items (service_plan_id, hymn_id, slot_type, item_status, note, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $sort = $this->nextSortOrder($planId);
        $now = $this->now();
        $stmt->execute([$planId, $hymnId, $slotType, $status, trim($note), $sort, $now, $now]);
    }

    public function updateItem(int $itemId, array $data): void
    {
        $stmt = $this->prepare('UPDATE service_plan_items SET slot_type = ?, item_status = ?, note = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([
            $data['slot_type'] ?? 'candidate',
            $data['item_status'] ?? 'candidate',
            trim((string) ($data['note'] ?? '')),
            $this->now(),
            $itemId,
        ]);
    }

    public function deleteItem(int $itemId): void
    {
        $stmt = $this->prepare('DELETE FROM service_plan_items WHERE id = ?');
        $stmt->execute([$itemId]);
    }

    public function selectedHymnIds(int $planId): array
    {
        $stmt = $this->prepare('SELECT DISTINCT hymn_id FROM service_plan_items WHERE service_plan_id = ? AND slot_type <> "candidate" AND item_status = "selected"');
        $stmt->execute([$planId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'hymn_id'));
    }

    private function nextSortOrder(int $planId): int
    {
        $stmt = $this->prepare('SELECT COALESCE(MAX(sort_order), 0) + 10 AS next_value FROM service_plan_items WHERE service_plan_id = ?');
        $stmt->execute([$planId]);
        $row = $stmt->fetch();
        return (int) ($row['next_value'] ?? 10);
    }
}
