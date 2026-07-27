<?php
declare(strict_types=1);

final class ProjectTasksController
{
    private PDO $pdo;
    private ProjectTask $tasks;
    private Project $projects;
    private ProjectPriority $priorities;

    public function __construct()
    {
        $this->pdo = db();
        $this->tasks = new ProjectTask($this->pdo);
        $this->projects = new Project($this->pdo);
        $this->priorities = new ProjectPriority($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $taskList = $this->tasks->listByProject($projectId);
        $editTask = null;
        if (!empty($_GET['edit_id'])) {
            $editTask = $this->tasks->find((int)$_GET['edit_id']);
        }
        $people = $this->peopleOptions();
        $priorities = $this->priorities->activeOptions();
        require APP_ROOT . '/app/views/project_tasks/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['task_name'] ?? '')) !== '') {
            $this->tasks->create($projectId, $this->collect(), current_person_id());
        }
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['task_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->tasks->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['task_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->tasks->delete($id);
        }
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'task_name' => trim((string)($_POST['task_name'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => (string)($_POST['status'] ?? 'not_started'),
            'priority' => (string)($_POST['priority'] ?? 'medium'),
            'assigned_to_person_id' => (int)($_POST['assigned_to_person_id'] ?? 0) ?: null,
            'start_date' => trim((string)($_POST['start_date'] ?? '')),
            'due_date' => trim((string)($_POST['due_date'] ?? '')),
            'parent_task_id' => (int)($_POST['parent_task_id'] ?? 0) ?: null,
        ];
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
