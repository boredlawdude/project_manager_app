<?php
declare(strict_types=1);

final class ProjectRisk
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, CONCAT(p.first_name, ' ', p.last_name) AS owner_name
            FROM project_risks r
            LEFT JOIN people p ON r.owner_person_id = p.person_id
            WHERE r.project_id = ?
            ORDER BY FIELD(r.status,'open','mitigating','realized','closed'), r.risk_id DESC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_risks WHERE risk_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_risks (
                project_id, title, description, category, likelihood, impact, status,
                owner_person_id, identified_date, mitigation_plan, review_date
            ) VALUES (
                :project_id, :title, :description, :category, :likelihood, :impact, :status,
                :owner_person_id, :identified_date, :mitigation_plan, :review_date
            )
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'title' => $d['title'],
            'description' => $d['description'] ?: null,
            'category' => $d['category'] ?: null,
            'likelihood' => $d['likelihood'],
            'impact' => $d['impact'],
            'status' => $d['status'],
            'owner_person_id' => $d['owner_person_id'] ?: null,
            'identified_date' => $d['identified_date'] ?: null,
            'mitigation_plan' => $d['mitigation_plan'] ?: null,
            'review_date' => $d['review_date'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $closedAt = in_array($d['status'], ['closed', 'realized'], true) ? ", closed_at = COALESCE(closed_at, NOW())" : ", closed_at = NULL";
        $stmt = $this->db->prepare("
            UPDATE project_risks SET
                title = :title, description = :description, category = :category,
                likelihood = :likelihood, impact = :impact, status = :status,
                owner_person_id = :owner_person_id, identified_date = :identified_date,
                mitigation_plan = :mitigation_plan, review_date = :review_date
                $closedAt
            WHERE risk_id = :risk_id
        ");
        $stmt->execute([
            'title' => $d['title'],
            'description' => $d['description'] ?: null,
            'category' => $d['category'] ?: null,
            'likelihood' => $d['likelihood'],
            'impact' => $d['impact'],
            'status' => $d['status'],
            'owner_person_id' => $d['owner_person_id'] ?: null,
            'identified_date' => $d['identified_date'] ?: null,
            'mitigation_plan' => $d['mitigation_plan'] ?: null,
            'review_date' => $d['review_date'] ?: null,
            'risk_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_risks WHERE risk_id = ?")->execute([$id]);
    }
}
