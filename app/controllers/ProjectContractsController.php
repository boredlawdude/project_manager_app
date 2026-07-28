<?php
declare(strict_types=1);

/**
 * Manages the link between a project and contracts in the sibling contracts_app
 * (shared database, separate codebase). Contracts themselves are still created
 * and edited in contracts_app — this controller only sets/clears contracts.project_id.
 */
final class ProjectContractsController
{
    private PDO $pdo;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) {
            http_response_code(404);
            echo 'Project not found.';
            return;
        }

        $linkedContracts = $this->linkedContracts($projectId);
        $unlinkedContracts = $this->unlinkedContracts();

        require APP_ROOT . '/app/views/project_contracts/index.php';
    }

    public function link(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $contractId = (int)($_POST['contract_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $projectId > 0 && $contractId > 0) {
            $stmt = $this->pdo->prepare(
                "UPDATE contracts SET project_id = :project_id WHERE contract_id = :contract_id AND project_id IS NULL"
            );
            $stmt->execute(['project_id' => $projectId, 'contract_id' => $contractId]);
        }

        header('Location: /index.php?page=project_contracts&project_id=' . $projectId);
        exit;
    }

    public function unlink(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $contractId = (int)($_POST['contract_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $projectId > 0 && $contractId > 0) {
            $stmt = $this->pdo->prepare(
                "UPDATE contracts SET project_id = NULL WHERE contract_id = :contract_id AND project_id = :project_id"
            );
            $stmt->execute(['contract_id' => $contractId, 'project_id' => $projectId]);
        }

        header('Location: /index.php?page=project_contracts&project_id=' . $projectId);
        exit;
    }

    private function linkedContracts(int $projectId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT c.contract_id, c.contract_number, c.name, c.total_contract_value, c.start_date, c.end_date,
                        cs.contract_status_name
                 FROM contracts c
                 LEFT JOIN contract_statuses cs ON c.contract_status_id = cs.contract_status_id
                 WHERE c.project_id = :project_id
                 ORDER BY c.name ASC"
            );
            $stmt->execute(['project_id' => $projectId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function unlinkedContracts(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT contract_id, contract_number, name
                 FROM contracts
                 WHERE project_id IS NULL
                 ORDER BY name ASC"
            );
            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $e) {
            return [];
        }
    }
}
