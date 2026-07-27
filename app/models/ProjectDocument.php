<?php
declare(strict_types=1);

final class ProjectDocument
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, CONCAT(p.first_name,' ',p.last_name) AS uploaded_by_name
            FROM project_documents d
            LEFT JOIN people p ON d.uploaded_by_person_id = p.person_id
            WHERE d.project_id = ?
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_documents WHERE project_document_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d, ?int $uploadedBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_documents (
                project_id, doc_type, file_name, file_path, mime_type, uploaded_by_person_id
            ) VALUES (
                :project_id, :doc_type, :file_name, :file_path, :mime_type, :uploaded_by_person_id
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'doc_type' => $d['doc_type'] ?: 'other',
            'file_name' => $d['file_name'],
            'file_path' => $d['file_path'],
            'mime_type' => $d['mime_type'] ?: null,
            'uploaded_by_person_id' => $uploadedBy,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_documents WHERE project_document_id = ?")->execute([$id]);
    }
}
