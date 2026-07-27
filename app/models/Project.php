<?php
declare(strict_types=1);

final class Project
{
    public function __construct(private PDO $db) {}

    public function search(array $filters = []): array
    {
        $sql = "
            SELECT
                p.*,
                d.department_name,
                CONCAT(pm.first_name, ' ', pm.last_name) AS project_manager_name
            FROM projects p
            LEFT JOIN departments d ON p.department_id = d.department_id
            LEFT JOIN people pm ON p.project_manager_person_id = pm.person_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['q'])) {
            $sql .= " AND (p.project_code LIKE :q OR p.project_name LIKE :q OR p.description LIKE :q)";
            $params['q'] = '%' . trim((string)$filters['q']) . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND p.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                d.department_name,
                CONCAT(pm.first_name, ' ', pm.last_name) AS project_manager_name,
                CONCAT(sp.first_name, ' ', sp.last_name) AS sponsor_name
            FROM projects p
            LEFT JOIN departments d ON p.department_id = d.department_id
            LEFT JOIN people pm ON p.project_manager_person_id = pm.person_id
            LEFT JOIN people sp ON p.sponsor_person_id = sp.person_id
            WHERE p.project_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO projects (
                project_code, project_name, description, status, priority,
                department_id, project_manager_person_id, sponsor_person_id,
                start_date, target_end_date, actual_end_date, estimated_budget,
                created_by_person_id
            ) VALUES (
                :project_code, :project_name, :description, :status, :priority,
                :department_id, :project_manager_person_id, :sponsor_person_id,
                :start_date, :target_end_date, :actual_end_date, :estimated_budget,
                :created_by_person_id
            )
        ");
        $stmt->execute([
            'project_code' => $data['project_code'],
            'project_name' => $data['project_name'],
            'description' => $data['description'] ?: null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'department_id' => $data['department_id'] ?: null,
            'project_manager_person_id' => $data['project_manager_person_id'] ?: null,
            'sponsor_person_id' => $data['sponsor_person_id'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'target_end_date' => $data['target_end_date'] ?: null,
            'actual_end_date' => $data['actual_end_date'] ?: null,
            'estimated_budget' => $data['estimated_budget'] !== '' ? $data['estimated_budget'] : null,
            'created_by_person_id' => $data['created_by_person_id'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE projects SET
                project_code = :project_code,
                project_name = :project_name,
                description = :description,
                status = :status,
                priority = :priority,
                department_id = :department_id,
                project_manager_person_id = :project_manager_person_id,
                sponsor_person_id = :sponsor_person_id,
                start_date = :start_date,
                target_end_date = :target_end_date,
                actual_end_date = :actual_end_date,
                estimated_budget = :estimated_budget
            WHERE project_id = :project_id
        ");
        $stmt->execute([
            'project_code' => $data['project_code'],
            'project_name' => $data['project_name'],
            'description' => $data['description'] ?: null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'department_id' => $data['department_id'] ?: null,
            'project_manager_person_id' => $data['project_manager_person_id'] ?: null,
            'sponsor_person_id' => $data['sponsor_person_id'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'target_end_date' => $data['target_end_date'] ?: null,
            'actual_end_date' => $data['actual_end_date'] ?: null,
            'estimated_budget' => $data['estimated_budget'] !== '' ? $data['estimated_budget'] : null,
            'project_id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM projects WHERE project_id = ?")->execute([$id]);
    }
}
