<?php

namespace App\Models;

use App\Core\Model;

class Hymn extends Model
{
    private array $fillable = [
        'tune_id', 'title_cn', 'title_en', 'alias', 'first_line', 'lyrics', 'ppt_lyrics', 'author',
        'composer', 'translator', 'source_book', 'hymn_number', 'key_signature', 'meter', 'scripture_refs',
        'doctrine_summary', 'usage_note', 'copyright_note', 'license_status', 'difficulty', 'familiarity',
        'tempo', 'status',
    ];

    public function search(array $filters = []): array
    {
        $sql = 'SELECT h.*, t.tune_name, t.tune_name_en,
                       tag_summary.tag_names
                FROM hymns h
                LEFT JOIN tunes t ON t.id = h.tune_id
                LEFT JOIN (
                    SELECT ht.hymn_id, GROUP_CONCAT(DISTINCT tags.name ORDER BY tags.sort_order ASC SEPARATOR " / ") AS tag_names
                    FROM hymn_tag ht
                    INNER JOIN tags ON tags.id = ht.tag_id
                    GROUP BY ht.hymn_id
                ) tag_summary ON tag_summary.hymn_id = h.id
                WHERE h.status <> "archived"';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND h.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $sql .= ' AND (
                h.title_cn LIKE ? OR
                h.title_en LIKE ? OR
                h.alias LIKE ? OR
                h.first_line LIKE ? OR
                h.lyrics LIKE ? OR
                h.ppt_lyrics LIKE ? OR
                h.scripture_refs LIKE ? OR
                h.doctrine_summary LIKE ? OR
                h.usage_note LIKE ? OR
                h.author LIKE ? OR
                h.composer LIKE ? OR
                h.translator LIKE ? OR
                t.tune_name LIKE ? OR
                t.tune_name_en LIKE ? OR
                EXISTS (
                    SELECT 1
                    FROM hymn_tag ht_search
                    INNER JOIN tags tags_search ON tags_search.id = ht_search.tag_id
                    WHERE ht_search.hymn_id = h.id AND tags_search.name LIKE ?
                )
            )';
            for ($i = 0; $i < 15; $i++) {
                $params[] = $q;
            }
        }

        if (!empty($filters['completeness_status'])) {
            $sql .= ' AND h.completeness_status = ?';
            $params[] = $filters['completeness_status'];
        }

        if (!empty($filters['tune_id'])) {
            $sql .= ' AND h.tune_id = ?';
            $params[] = (int) $filters['tune_id'];
        }

        if (!empty($filters['difficulty'])) {
            $sql .= ' AND h.difficulty = ?';
            $params[] = (int) $filters['difficulty'];
        }

        if (!empty($filters['familiarity'])) {
            $sql .= ' AND h.familiarity = ?';
            $params[] = (int) $filters['familiarity'];
        }

        if (!empty($filters['missing_field'])) {
            $sql .= ' AND FIND_IN_SET(?, h.missing_fields)';
            $params[] = $filters['missing_field'];
        }

        if (!empty($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            $tagIds = array_values(array_filter(array_map('intval', $filters['tag_ids'])));
            if ($tagIds) {
                $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
                $sql .= " AND h.id IN (
                    SELECT hymn_id
                    FROM hymn_tag
                    WHERE tag_id IN ($placeholders)
                    GROUP BY hymn_id
                    HAVING COUNT(DISTINCT tag_id) = " . count($tagIds) . '
                )';
                foreach ($tagIds as $tagId) {
                    $params[] = $tagId;
                }
            }
        }

        if (!empty($filters['q'])) {
            $like = '%' . trim($filters['q']) . '%';
            $sql .= ' ORDER BY
                (CASE
                    WHEN h.title_cn LIKE ? THEN 100
                    WHEN h.title_en LIKE ? THEN 95
                    WHEN h.alias LIKE ? THEN 90
                    WHEN h.first_line LIKE ? THEN 80
                    WHEN EXISTS (
                        SELECT 1
                        FROM hymn_tag ht_rank
                        INNER JOIN tags tags_rank ON tags_rank.id = ht_rank.tag_id
                        WHERE ht_rank.hymn_id = h.id AND tags_rank.name LIKE ?
                    ) THEN 70
                    WHEN t.tune_name LIKE ? THEN 60
                    WHEN h.scripture_refs LIKE ? THEN 50
                    WHEN h.lyrics LIKE ? THEN 40
                    ELSE 10
                 END) DESC,
                 h.updated_at DESC';
            for ($i = 0; $i < 8; $i++) {
                $params[] = $like;
            }
        } else {
            $sql .= ' ORDER BY h.updated_at DESC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function latest(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT id, title_cn, first_line, completeness_status, completeness_score, missing_fields, created_at FROM hymns WHERE status <> "archived" ORDER BY created_at DESC LIMIT ' . (int) $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function incomplete(int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT id, title_cn, completeness_status, completeness_score, missing_fields FROM hymns WHERE completeness_status IN ("draft", "incomplete") AND status <> "archived" ORDER BY updated_at DESC LIMIT ' . (int) $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function recentUsed(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT id, title_cn, last_used_at, used_count FROM hymns WHERE last_used_at IS NOT NULL AND status <> "archived" ORDER BY last_used_at DESC LIMIT ' . (int) $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function counts(): array
    {
        $row = $this->db->query('SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN completeness_status IN ("draft", "incomplete") THEN 1 ELSE 0 END) AS incomplete_total,
            SUM(CASE WHEN status = "hidden" THEN 1 ELSE 0 END) AS hidden_total
            FROM hymns WHERE status <> "archived"')->fetch();

        return $row ?: ['total' => 0, 'incomplete_total' => 0, 'hidden_total' => 0];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT h.*, t.tune_name, t.tune_name_en, t.composer AS tune_composer, t.meter AS tune_meter, t.key_signature AS tune_key_signature, t.tempo AS tune_tempo
                                    FROM hymns h
                                    LEFT JOIN tunes t ON t.id = h.tune_id
                                    WHERE h.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $hymn = $stmt->fetch();
        if (!$hymn) {
            return null;
        }

        $tagStmt = $this->db->prepare('SELECT t.*, g.name AS group_name, g.code AS group_code
                                       FROM hymn_tag ht
                                       INNER JOIN tags t ON t.id = ht.tag_id
                                       INNER JOIN tag_groups g ON g.id = t.group_id
                                       WHERE ht.hymn_id = ?
                                       ORDER BY g.sort_order ASC, t.sort_order ASC');
        $tagStmt->execute([$id]);
        $hymn['tags'] = $tagStmt->fetchAll();

        $fileStmt = $this->db->prepare('SELECT * FROM hymn_files WHERE hymn_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC');
        $fileStmt->execute([$id]);
        $hymn['files'] = $fileStmt->fetchAll();

        if (!empty($hymn['tune_id'])) {
            $sameTune = $this->db->prepare('SELECT id, title_cn, first_line FROM hymns WHERE tune_id = ? AND id <> ? AND status <> "archived" ORDER BY title_cn ASC');
            $sameTune->execute([$hymn['tune_id'], $id]);
            $hymn['same_tune_hymns'] = $sameTune->fetchAll();
        } else {
            $hymn['same_tune_hymns'] = [];
        }

        return $hymn;
    }

    public function create(array $data, array $tagIds): int
    {
        $record = $this->normalize($data);
        $columns = array_keys($record);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO hymns (' . implode(',', $columns) . ') VALUES (' . $placeholders . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($record));
        $id = (int) $this->db->lastInsertId();
        $this->syncTags($id, $tagIds);
        $this->refreshCompleteness($id);
        return $id;
    }

    public function updateHymn(int $id, array $data, array $tagIds): void
    {
        $record = $this->normalize($data, false);
        $sets = [];
        foreach (array_keys($record) as $column) {
            $sets[] = $column . ' = ?';
        }
        $sql = 'UPDATE hymns SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $values = array_values($record);
        $values[] = $id;
        $stmt->execute($values);
        $this->syncTags($id, $tagIds);
        $this->refreshCompleteness($id);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE hymns SET status = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$status, $this->now(), $id]);
    }

    public function refreshCompleteness(int $id): void
    {
        $stmt = $this->db->prepare('SELECT * FROM hymns WHERE id = ?');
        $stmt->execute([$id]);
        $hymn = $stmt->fetch();
        if (!$hymn) {
            return;
        }

        $tagsStmt = $this->db->prepare('SELECT t.id, g.code AS group_code FROM hymn_tag ht INNER JOIN tags t ON t.id = ht.tag_id INNER JOIN tag_groups g ON g.id = t.group_id WHERE ht.hymn_id = ?');
        $tagsStmt->execute([$id]);
        $tags = $tagsStmt->fetchAll();

        $filesStmt = $this->db->prepare('SELECT id FROM hymn_files WHERE hymn_id = ?');
        $filesStmt->execute([$id]);
        $files = $filesStmt->fetchAll();

        $completeness = calculateHymnCompleteness($hymn, $tags, $files);
        $update = $this->db->prepare('UPDATE hymns SET completeness_score = ?, completeness_status = ?, missing_fields = ?, updated_at = ? WHERE id = ?');
        $update->execute([
            $completeness['score'],
            $completeness['status'],
            implode(',', $completeness['missing']),
            $this->now(),
            $id,
        ]);
    }

    public function markUsed(array $hymnIds): void
    {
        if (!$hymnIds) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($hymnIds), '?'));
        $params = array_merge([$this->now()], array_values($hymnIds));
        $stmt = $this->db->prepare("UPDATE hymns SET last_used_at = ?, used_count = used_count + 1 WHERE id IN ($placeholders)");
        $stmt->execute($params);
    }

    private function normalize(array $data, bool $isCreate = true): array
    {
        $now = $this->now();
        $record = [];
        foreach ($this->fillable as $field) {
            if ($field === 'tune_id') {
                $record[$field] = !empty($data[$field]) ? (int) $data[$field] : null;
                continue;
            }

            if (in_array($field, ['difficulty', 'familiarity'], true)) {
                $record[$field] = (int) ($data[$field] ?? 3);
                continue;
            }

            $value = isset($data[$field]) ? trim((string) $data[$field]) : null;
            $record[$field] = $value === '' ? null : $value;
        }

        if (empty($record['title_cn'])) {
            throw new \InvalidArgumentException('中文诗歌名不能为空。');
        }

        if ($record['status'] === null) {
            $record['status'] = 'active';
        }

        if ($isCreate) {
            $record['completeness_status'] = 'draft';
            $record['completeness_score'] = 0;
            $record['missing_fields'] = null;
            $record['used_count'] = 0;
            $record['created_at'] = $now;
        }
        $record['updated_at'] = $now;

        return $record;
    }

    private function syncTags(int $hymnId, array $tagIds): void
    {
        $tagIds = array_values(array_unique(array_filter(array_map('intval', $tagIds))));
        $delete = $this->db->prepare('DELETE FROM hymn_tag WHERE hymn_id = ?');
        $delete->execute([$hymnId]);

        if (!$tagIds) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO hymn_tag (hymn_id, tag_id, created_at) VALUES (?, ?, ?)');
        $now = $this->now();
        foreach ($tagIds as $tagId) {
            $stmt->execute([$hymnId, $tagId, $now]);
        }
    }
}
