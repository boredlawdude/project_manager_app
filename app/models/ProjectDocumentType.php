<?php
declare(strict_types=1);

final class ProjectDocumentType
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM project_document_types ORDER BY sort_order ASC, type_name ASC");
        return $stmt->fetchAll();
    }

    public function activeOptions(): array
    {
        $stmt = $this->db->query("SELECT type_name, description FROM project_document_types WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function create(string $name, string $desc): bool
    {
        $stmt = $this->db->prepare("INSERT INTO project_document_types (type_name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $desc]);
    }

    public function update(int $id, string $name, string $desc, bool $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE project_document_types SET type_name = ?, description = ?, is_active = ? WHERE document_type_id = ?");
        return $stmt->execute([$name, $desc, $isActive ? 1 : 0, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_document_types WHERE document_type_id = ?");
        return $stmt->execute([$id]);
    }
}
