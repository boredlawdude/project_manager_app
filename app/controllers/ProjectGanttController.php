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
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $taskList = $this->tasks->listByProject($projectId);
        [$ganttTasks, $skippedCount] = $this->buildGanttTasks($taskList);
        require APP_ROOT . '/app/views/project_gantt/index.php';
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

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: int} [gantt task rows, count of tasks skipped for missing dates]
     */
    private function buildGanttTasks(array $taskList): array
    {
        $progressMap = ['not_started' => 0, 'in_progress' => 50, 'blocked' => 25, 'completed' => 100, 'cancelled' => 100];
        $items = [];
        $skipped = 0;

        foreach ($taskList as $t) {
            $start = $t['start_date'] ?: $t['due_date'];
            $end = $t['due_date'] ?: $t['start_date'];
            if (!$start || !$end) {
                $skipped++;
                continue;
            }
            $statusClass = $t['status'] === 'blocked' ? 'gantt-task-blocked'
                : ($t['status'] === 'completed' ? 'gantt-task-done' : '');
            $items[(string)$t['task_id']] = [
                'id' => (string)$t['task_id'],
                'name' => $t['task_name'],
                'start' => $start,
                'end' => $end,
                'progress' => $progressMap[$t['status']] ?? 0,
                'dependencies' => '',
                'custom_class' => $statusClass,
                '_depends_on' => ($t['dependency_type'] ?? 'independent') === 'dependent' ? $t['depends_on_task_id'] : null,
            ];
        }

        // Only wire up a dependency arrow if the prerequisite task is also shown on the chart.
        foreach ($items as &$item) {
            $dep = $item['_depends_on'];
            if ($dep !== null && isset($items[(string)$dep])) {
                $item['dependencies'] = (string)$dep;
            }
            unset($item['_depends_on']);
        }
        unset($item);

        return [array_values($items), $skipped];
    }
}
