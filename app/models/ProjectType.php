<?php
declare(strict_types=1);

final class ProjectType
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM project_types ORDER BY sort_order ASC, project_type_name ASC");
        return $stmt->fetchAll();
    }

    public function activeOptions(): array
    {
        $stmt = $this->db->query("SELECT project_type_id, project_type_name FROM project_types WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_types WHERE project_type_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $description): int
    {
        $stmt = $this->db->prepare("INSERT INTO project_types (project_type_name, project_type_description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description, bool $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE project_types SET project_type_name = ?, project_type_description = ?, is_active = ? WHERE project_type_id = ?");
        return $stmt->execute([$name, $description, $isActive ? 1 : 0, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_types WHERE project_type_id = ?");
        return $stmt->execute([$id]);
    }
}
