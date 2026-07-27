<?php
declare(strict_types=1);

final class ProjectTeamController
{
    private PDO $pdo;
    private ProjectTeamMember $team;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->team = new ProjectTeamMember($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $memberList = $this->team->listByProject($projectId);
        $people = $this->peopleOptions();
        require APP_ROOT . '/app/views/project_team/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $personId = (int)($_POST['person_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $personId > 0) {
            $this->team->add(
                $projectId,
                $personId,
                trim((string)($_POST['project_role'] ?? '')),
                !empty($_POST['is_lead'])
            );
        }
        header('Location: /index.php?page=project_team&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $personId = (int)($_GET['person_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->team->remove($projectId, $personId);
        }
        header('Location: /index.php?page=project_team&project_id=' . $projectId);
        exit;
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
