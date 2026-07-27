<?php
declare(strict_types=1);

final class ProjectFundingController
{
    private PDO $pdo;
    private ProjectFundingSource $funding;
    private Project $projects;
    private ProjectFundingSourceType $sourceTypes;

    public function __construct()
    {
        $this->pdo = db();
        $this->funding = new ProjectFundingSource($this->pdo);
        $this->projects = new Project($this->pdo);
        $this->sourceTypes = new ProjectFundingSourceType($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $fundingList = $this->funding->listByProject($projectId);
        $editFunding = !empty($_GET['edit_id']) ? $this->funding->find((int)$_GET['edit_id']) : null;
        $fundingSourceTypes = $this->sourceTypes->activeOptions();
        require APP_ROOT . '/app/views/project_funding/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['source_name'] ?? '')) !== '') {
            $this->funding->create($projectId, $this->collect());
        }
        header('Location: /index.php?page=project_funding&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['funding_source_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->funding->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_funding&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['funding_source_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->funding->delete($id);
        }
        header('Location: /index.php?page=project_funding&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'source_name' => trim((string)($_POST['source_name'] ?? '')),
            'source_type' => (string)($_POST['source_type'] ?? 'other'),
            'grant_number' => trim((string)($_POST['grant_number'] ?? '')),
            'awarded_amount' => trim((string)($_POST['awarded_amount'] ?? '')),
            'received_amount' => trim((string)($_POST['received_amount'] ?? '')),
            'status' => (string)($_POST['status'] ?? 'anticipated'),
            'expiration_date' => trim((string)($_POST['expiration_date'] ?? '')),
            'notes' => trim((string)($_POST['notes'] ?? '')),
        ];
    }
}
