<?php
declare(strict_types=1);

final class ProjectTypeDefaultTask
{
    public function __construct(private PDO $db) {}

    public function listByType(int $projectTypeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_type_default_tasks WHERE project_type_id = ? ORDER BY sort_order ASC, task_name ASC");
        $stmt->execute([$projectTypeId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_type_default_tasks WHERE default_task_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $projectTypeId, string $taskName, string $description): int
    {
        $stmt = $this->db->prepare("INSERT INTO project_type_default_tasks (project_type_id, task_name, description) VALUES (?, ?, ?)");
        $stmt->execute([$projectTypeId, $taskName, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $taskName, string $description): bool
    {
        $stmt = $this->db->prepare("UPDATE project_type_default_tasks SET task_name = ?, description = ? WHERE default_task_id = ?");
        return $stmt->execute([$taskName, $description, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_type_default_tasks WHERE default_task_id = ?");
        return $stmt->execute([$id]);
    }
}
