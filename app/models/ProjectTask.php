<?php
declare(strict_types=1);

final class ProjectTask
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, CONCAT(p.first_name, ' ', p.last_name) AS assignee_name
            FROM project_tasks t
            LEFT JOIN people p ON t.assigned_to_person_id = p.person_id
            WHERE t.project_id = ?
            ORDER BY t.sort_order ASC, t.due_date IS NULL, t.due_date ASC, t.task_id ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d, ?int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_tasks (
                project_id, parent_task_id, task_name, description, status, priority,
                assigned_to_person_id, start_date, due_date, created_by_person_id
            ) VALUES (
                :project_id, :parent_task_id, :task_name, :description, :status, :priority,
                :assigned_to_person_id, :start_date, :due_date, :created_by_person_id
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'parent_task_id' => $d['parent_task_id'] ?: null,
            'task_name' => $d['task_name'],
            'description' => $d['description'] ?: null,
            'status' => $d['status'],
            'priority' => $d['priority'],
            'assigned_to_person_id' => $d['assigned_to_person_id'] ?: null,
            'start_date' => $d['start_date'] ?: null,
            'due_date' => $d['due_date'] ?: null,
            'created_by_person_id' => $createdBy,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $completedAt = $d['status'] === 'completed' ? ", completed_at = COALESCE(completed_at, NOW())" : ", completed_at = NULL";
        $stmt = $this->db->prepare("
            UPDATE project_tasks SET
                task_name = :task_name,
                description = :description,
                status = :status,
                priority = :priority,
                assigned_to_person_id = :assigned_to_person_id,
                start_date = :start_date,
                due_date = :due_date
                $completedAt
            WHERE task_id = :task_id
        ");
        $stmt->execute([
            'task_name' => $d['task_name'],
            'description' => $d['description'] ?: null,
            'status' => $d['status'],
            'priority' => $d['priority'],
            'assigned_to_person_id' => $d['assigned_to_person_id'] ?: null,
            'start_date' => $d['start_date'] ?: null,
            'due_date' => $d['due_date'] ?: null,
            'task_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_tasks WHERE task_id = ?")->execute([$id]);
    }
}
