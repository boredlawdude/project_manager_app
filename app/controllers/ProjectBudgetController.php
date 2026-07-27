<?php
declare(strict_types=1);

final class ProjectBudgetController
{
    private PDO $pdo;
    private ProjectBudgetLine $budget;
    private ProjectFundingSource $funding;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->budget = new ProjectBudgetLine($this->pdo);
        $this->funding = new ProjectFundingSource($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $budgetList = $this->budget->listByProject($projectId);
        $totals = $this->budget->totals($projectId);
        $editLine = !empty($_GET['edit_id']) ? $this->budget->find((int)$_GET['edit_id']) : null;
        $fundingOptions = $this->funding->options($projectId);
        $contractOptions = $this->contractOptions($projectId);
        require APP_ROOT . '/app/views/project_budget/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['line_name'] ?? '')) !== '') {
            $this->budget->create($projectId, $this->collect());
        }
        header('Location: /index.php?page=project_budget&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['budget_line_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->budget->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_budget&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['budget_line_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->budget->delete($id);
        }
        header('Location: /index.php?page=project_budget&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'line_name' => trim((string)($_POST['line_name'] ?? '')),
            'category' => trim((string)($_POST['category'] ?? '')),
            'fiscal_year' => trim((string)($_POST['fiscal_year'] ?? '')),
            'budgeted_amount' => trim((string)($_POST['budgeted_amount'] ?? '')),
            'committed_amount' => trim((string)($_POST['committed_amount'] ?? '')),
            'actual_amount' => trim((string)($_POST['actual_amount'] ?? '')),
            'funding_source_id' => (int)($_POST['funding_source_id'] ?? 0) ?: null,
            'contract_id' => (int)($_POST['contract_id'] ?? 0) ?: null,
            'notes' => trim((string)($_POST['notes'] ?? '')),
        ];
    }

    /** Contracts already linked to this project (via contracts.project_id, set in contracts_app) */
    private function contractOptions(int $projectId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT contract_id, contract_number, name FROM contracts WHERE project_id = ? ORDER BY name");
            $stmt->execute([$projectId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
