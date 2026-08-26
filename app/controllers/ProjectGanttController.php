<?php
declare(strict_types=1);

final class ProjectGanttController
{
    private PDO $pdo;
    private ProjectTask $tasks;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->tasks = new ProjectTask($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        // The Gantt chart now lives inline on the Tasks page instead of its own tab.
        $projectId = (int)($_GET['project_id'] ?? 0);
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    public function updateDates(): void
    {
        header('Content-Type: application/json');

        $taskId = (int)($_POST['task_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $dueDate = trim((string)($_POST['due_date'] ?? ''));

        if ($taskId <= 0 || $projectId <= 0 || $startDate === '' || $dueDate === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Missing task_id, project_id, start_date, or due_date.']);
            return;
        }

        $dateRe = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRe, $startDate) || !preg_match($dateRe, $dueDate)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Dates must be in YYYY-MM-DD format.']);
            return;
        }
        if ($startDate > $dueDate) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Start date cannot be after due date.']);
            return;
        }

        $task = $this->tasks->find($taskId);
        if (!$task || (int)$task['project_id'] !== $projectId) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Task not found for this project.']);
            return;
        }

        $this->tasks->updateDates($taskId, $startDate, $dueDate);
        echo json_encode(['ok' => true]);
    }
}
