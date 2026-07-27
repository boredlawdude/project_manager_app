<?php
declare(strict_types=1);

final class ProjectTimelineMilestone
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM project_timeline_milestones
            WHERE project_id = ?
            ORDER BY sort_order ASC, target_date IS NULL, target_date ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_timeline_milestones WHERE milestone_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_timeline_milestones (
                project_id, milestone_name, description, target_date, actual_date, status, sort_order
            ) VALUES (
                :project_id, :milestone_name, :description, :target_date, :actual_date, :status, :sort_order
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'milestone_name' => $d['milestone_name'],
            'description' => $d['description'] ?: null,
            'target_date' => $d['target_date'] ?: null,
            'actual_date' => $d['actual_date'] ?: null,
            'status' => $d['status'],
            'sort_order' => $d['sort_order'] !== '' ? (int)$d['sort_order'] : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_timeline_milestones SET
                milestone_name = :milestone_name, description = :description, target_date = :target_date,
                actual_date = :actual_date, status = :status, sort_order = :sort_order
            WHERE milestone_id = :milestone_id
        ");
        $stmt->execute([
            'milestone_name' => $d['milestone_name'],
            'description' => $d['description'] ?: null,
            'target_date' => $d['target_date'] ?: null,
            'actual_date' => $d['actual_date'] ?: null,
            'status' => $d['status'],
            'sort_order' => $d['sort_order'] !== '' ? (int)$d['sort_order'] : 0,
            'milestone_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_timeline_milestones WHERE milestone_id = ?")->execute([$id]);
    }
}
