<?php
declare(strict_types=1);

final class ProjectFundingSource
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_funding_sources WHERE project_id = ? ORDER BY funding_source_id DESC");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function options(int $projectId): array
    {
        $stmt = $this->db->prepare("SELECT funding_source_id, source_name FROM project_funding_sources WHERE project_id = ? ORDER BY source_name");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_funding_sources WHERE funding_source_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_funding_sources (
                project_id, source_name, source_type, grant_number, awarded_amount,
                received_amount, status, expiration_date, notes
            ) VALUES (
                :project_id, :source_name, :source_type, :grant_number, :awarded_amount,
                :received_amount, :status, :expiration_date, :notes
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'source_name' => $d['source_name'],
            'source_type' => $d['source_type'],
            'grant_number' => $d['grant_number'] ?: null,
            'awarded_amount' => $d['awarded_amount'] !== '' ? $d['awarded_amount'] : null,
            'received_amount' => $d['received_amount'] !== '' ? $d['received_amount'] : 0,
            'status' => $d['status'],
            'expiration_date' => $d['expiration_date'] ?: null,
            'notes' => $d['notes'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_funding_sources SET
                source_name = :source_name, source_type = :source_type, grant_number = :grant_number,
                awarded_amount = :awarded_amount, received_amount = :received_amount, status = :status,
                expiration_date = :expiration_date, notes = :notes
            WHERE funding_source_id = :funding_source_id
        ");
        $stmt->execute([
            'source_name' => $d['source_name'],
            'source_type' => $d['source_type'],
            'grant_number' => $d['grant_number'] ?: null,
            'awarded_amount' => $d['awarded_amount'] !== '' ? $d['awarded_amount'] : null,
            'received_amount' => $d['received_amount'] !== '' ? $d['received_amount'] : 0,
            'status' => $d['status'],
            'expiration_date' => $d['expiration_date'] ?: null,
            'notes' => $d['notes'] ?: null,
            'funding_source_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_funding_sources WHERE funding_source_id = ?")->execute([$id]);
    }
}
