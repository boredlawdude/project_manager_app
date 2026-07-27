<?php
declare(strict_types=1);

final class ProjectTeamMember
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, CONCAT(p.first_name,' ',p.last_name) AS person_name, p.email, p.title
            FROM project_team_members m
            JOIN people p ON p.person_id = m.person_id
            WHERE m.project_id = ?
            ORDER BY m.is_lead DESC, person_name
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function add(int $projectId, int $personId, ?string $role, bool $isLead): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_team_members (project_id, person_id, project_role, is_lead)
            VALUES (:project_id, :person_id, :project_role, :is_lead)
            ON DUPLICATE KEY UPDATE project_role = VALUES(project_role), is_lead = VALUES(is_lead)
        ");
        $stmt->execute([
            'project_id' => $projectId,
            'person_id' => $personId,
            'project_role' => $role ?: null,
            'is_lead' => $isLead ? 1 : 0,
        ]);
    }

    public function remove(int $projectId, int $personId): void
    {
        $stmt = $this->db->prepare("DELETE FROM project_team_members WHERE project_id = ? AND person_id = ?");
        $stmt->execute([$projectId, $personId]);
    }
}
