<?php
declare(strict_types=1);

final class ProjectNote
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, CONCAT(p.first_name, ' ', p.last_name) AS author_name
            FROM project_notes n
            LEFT JOIN people p ON n.person_id = p.person_id
            WHERE n.project_id = ?
            ORDER BY n.note_date DESC, n.note_id DESC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_notes WHERE note_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_notes (project_id, person_id, note_date, notes)
            VALUES (:project_id, :person_id, :note_date, :notes)
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'person_id' => $d['person_id'] ?: null,
            'note_date' => $d['note_date'] ?: date('Y-m-d'),
            'notes' => $d['notes'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_notes SET
                note_date = :note_date,
                notes = :notes
            WHERE note_id = :note_id
        ");
        $stmt->execute([
            'note_date' => $d['note_date'] ?: date('Y-m-d'),
            'notes' => $d['notes'],
            'note_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_notes WHERE note_id = ?")->execute([$id]);
    }
}
