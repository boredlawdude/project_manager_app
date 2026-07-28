<?php
declare(strict_types=1);

final class ProjectBudgetLine
{
    public function __construct(private PDO $db) {}

    public function listByProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, f.source_name AS funding_source_name
            FROM project_budget_lines b
            LEFT JOIN project_funding_sources f ON b.funding_source_id = f.funding_source_id
            WHERE b.project_id = ?
            ORDER BY b.fiscal_year DESC, b.budget_line_id DESC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_budget_lines WHERE budget_line_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, array $d): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_budget_lines (
                project_id, line_name, category, fiscal_year, budgeted_amount,
                committed_amount, actual_amount, funding_source_id, notes
            ) VALUES (
                :project_id, :line_name, :category, :fiscal_year, :budgeted_amount,
                :committed_amount, :actual_amount, :funding_source_id, :notes
            )
        ");
        $stmt->execute($this->params($projectId, $d));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare("
            UPDATE project_budget_lines SET
                line_name = :line_name, category = :category, fiscal_year = :fiscal_year,
                budgeted_amount = :budgeted_amount, committed_amount = :committed_amount,
                actual_amount = :actual_amount, funding_source_id = :funding_source_id,
                notes = :notes
            WHERE budget_line_id = :budget_line_id
        ");
        $params = $this->params(null, $d);
        unset($params['project_id']);
        $params['budget_line_id'] = $id;
        $stmt->execute($params);
    }

    private function params(?int $projectId, array $d): array
    {
        $params = [
            'line_name' => $d['line_name'],
            'category' => $d['category'] ?: null,
            'fiscal_year' => $d['fiscal_year'] ?: null,
            'budgeted_amount' => $d['budgeted_amount'] !== '' ? $d['budgeted_amount'] : 0,
            'committed_amount' => $d['committed_amount'] !== '' ? $d['committed_amount'] : 0,
            'actual_amount' => $d['actual_amount'] !== '' ? $d['actual_amount'] : 0,
            'funding_source_id' => $d['funding_source_id'] ?: null,
            'notes' => $d['notes'] ?: null,
        ];
        if ($projectId !== null) {
            $params['project_id'] = $projectId;
        }
        return $params;
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM project_budget_lines WHERE budget_line_id = ?")->execute([$id]);
    }

    public function totals(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(budgeted_amount),0) AS budgeted, COALESCE(SUM(committed_amount),0) AS committed,
                   COALESCE(SUM(actual_amount),0) AS actual
            FROM project_budget_lines WHERE project_id = ?
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetch() ?: ['budgeted' => 0, 'committed' => 0, 'actual' => 0];
    }
}
