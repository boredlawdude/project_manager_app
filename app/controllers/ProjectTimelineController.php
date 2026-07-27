<?php
declare(strict_types=1);

final class ProjectTimelineController
{
    private PDO $pdo;
    private ProjectTimelineMilestone $milestones;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->milestones = new ProjectTimelineMilestone($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $milestoneList = $this->milestones->listByProject($projectId);
        $editMilestone = !empty($_GET['edit_id']) ? $this->milestones->find((int)$_GET['edit_id']) : null;
        require APP_ROOT . '/app/views/project_timeline/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['milestone_name'] ?? '')) !== '') {
            $this->milestones->create($projectId, $this->collect());
        }
        header('Location: /index.php?page=project_timeline&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['milestone_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->milestones->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_timeline&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['milestone_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->milestones->delete($id);
        }
        header('Location: /index.php?page=project_timeline&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'milestone_name' => trim((string)($_POST['milestone_name'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'target_date' => trim((string)($_POST['target_date'] ?? '')),
            'actual_date' => trim((string)($_POST['actual_date'] ?? '')),
            'status' => (string)($_POST['status'] ?? 'pending'),
            'sort_order' => trim((string)($_POST['sort_order'] ?? '0')),
        ];
    }
}
