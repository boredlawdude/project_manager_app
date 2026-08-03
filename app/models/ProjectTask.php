<?php
declare(strict_types=1);

final class ProjectTask
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, CONCAT(p.first_name, ' ', p.last_name) AS assignee_name,
                   dep.task_name AS depends_on_task_name, dep.status AS depends_on_status
            FROM project_tasks t
            LEFT JOIN people p ON t.assigned_to_person_id = p.person_id
            LEFT JOIN project_tasks dep ON t.depends_on_task_id = dep.task_id
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

    public function listByAssignee(int $personId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, p.project_id AS project_id, p.project_name, p.project_code
            FROM project_tasks t
            JOIN projects p ON p.project_id = t.project_id
            WHERE t.assigned_to_person_id = ?
              AND t.status NOT IN ('completed', 'cancelled')
            ORDER BY t.due_date IS NULL, t.due_date ASC, t.task_id ASC
        ");
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }

    public function create(int $projectId, array $d, ?int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_tasks (
                project_id, parent_task_id, task_name, description, status, priority,
                dependency_type, depends_on_task_id,
                assigned_to_person_id, start_date, due_date, created_by_person_id
            ) VALUES (
                :project_id, :parent_task_id, :task_name, :description, :status, :priority,
                :dependency_type, :depends_on_task_id,
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
            'dependency_type' => $d['dependency_type'] ?? 'independent',
            'depends_on_task_id' => $d['depends_on_task_id'] ?: null,
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
                dependency_type = :dependency_type,
                depends_on_task_id = :depends_on_task_id,
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
            'dependency_type' => $d['dependency_type'] ?? 'independent',
            'depends_on_task_id' => $d['depends_on_task_id'] ?: null,
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

    public function updateDates(int $id, string $startDate, string $dueDate): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_tasks SET start_date = :start_date, due_date = :due_date
            WHERE task_id = :task_id
        ");
        $stmt->execute([
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'task_id' => $id,
        ]);
    }

    /**
     * Walks the depends_on chain starting at $candidateDependsOnId to see whether
     * it ever leads back to $taskId - i.e. whether making $taskId depend on
     * $candidateDependsOnId would create a circular dependency.
     */
    public function wouldCreateCycle(int $taskId, int $candidateDependsOnId): bool
    {
        if ($taskId === $candidateDependsOnId) {
            return true;
        }
        $currentId = $candidateDependsOnId;
        $visited = [];
        $guard = 0;
        while ($currentId !== null && $guard++ < 200) {
            if ($currentId === $taskId) {
                return true;
            }
            if (isset($visited[$currentId])) {
                return false;
            }
            $visited[$currentId] = true;
            $row = $this->find($currentId);
            $currentId = $row['depends_on_task_id'] !== null ? (int)$row['depends_on_task_id'] : null;
        }
        return false;
    }

    /**
     * Candidate tasks selectable in the "depends on" dropdown for a project:
     * all tasks in the project except itself and any that would create a
     * circular dependency chain.
     */
    public function dependencyOptions(int $projectId, ?int $excludeTaskId): array
    {
        $options = [];
        foreach ($this->listByProject($projectId) as $t) {
            $tid = (int)$t['task_id'];
            if ($excludeTaskId !== null) {
                if ($tid === $excludeTaskId) {
                    continue;
                }
                if ($this->wouldCreateCycle($excludeTaskId, $tid)) {
                    continue;
                }
            }
            $options[] = $t;
        }
        return $options;
    }
}
