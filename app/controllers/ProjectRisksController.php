<?php
declare(strict_types=1);

final class ProjectRisksController
{
    private PDO $pdo;
    private ProjectRisk $risks;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->risks = new ProjectRisk($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $riskList = $this->risks->listByProject($projectId);
        $editRisk = !empty($_GET['edit_id']) ? $this->risks->find((int)$_GET['edit_id']) : null;
        $people = $this->peopleOptions();
        require APP_ROOT . '/app/views/project_risks/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['title'] ?? '')) !== '') {
            $this->risks->create($projectId, $this->collect());
        }
        header('Location: /index.php?page=project_risks&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['risk_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->risks->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_risks&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['risk_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->risks->delete($id);
        }
        header('Location: /index.php?page=project_risks&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'category' => trim((string)($_POST['category'] ?? '')),
            'likelihood' => (string)($_POST['likelihood'] ?? 'medium'),
            'impact' => (string)($_POST['impact'] ?? 'medium'),
            'status' => (string)($_POST['status'] ?? 'open'),
            'owner_person_id' => (int)($_POST['owner_person_id'] ?? 0) ?: null,
            'identified_date' => trim((string)($_POST['identified_date'] ?? '')),
            'mitigation_plan' => trim((string)($_POST['mitigation_plan'] ?? '')),
            'review_date' => trim((string)($_POST['review_date'] ?? '')),
        ];
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
