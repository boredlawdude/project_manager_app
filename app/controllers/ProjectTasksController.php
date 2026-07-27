<?php
declare(strict_types=1);

final class ProjectTasksController
{
    private PDO $pdo;
    private ProjectTask $tasks;
    private Project $projects;
    private ProjectPriority $priorities;
    private ProjectTypeDefaultTask $defaultTasks;

    public function __construct()
    {
        $this->pdo = db();
        $this->tasks = new ProjectTask($this->pdo);
        $this->projects = new Project($this->pdo);
        $this->priorities = new ProjectPriority($this->pdo);
        $this->defaultTasks = new ProjectTypeDefaultTask($this->pdo);
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
        $dependencyOptions = $this->tasks->dependencyOptions($projectId, $editTask['task_id'] ?? null);
        $taskErrors = $_SESSION['task_errors'] ?? [];
        unset($_SESSION['task_errors']);

        $importableDefaultTasks = [];
        if (!empty($project['project_type_id'])) {
            $existingNames = array_map(
                static fn(array $t): string => mb_strtolower(trim((string)$t['task_name'])),
                $taskList
            );
            foreach ($this->defaultTasks->listByType((int)$project['project_type_id']) as $dt) {
                $dt['already_added'] = in_array(mb_strtolower(trim((string)$dt['task_name'])), $existingNames, true);
                $importableDefaultTasks[] = $dt;
            }
        }

        require APP_ROOT . '/app/views/project_tasks/index.php';
    }

    public function importDefaults(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project && !empty($project['project_type_id'])) {
            $existingNames = array_map(
                static fn(array $t): string => mb_strtolower(trim((string)$t['task_name'])),
                $this->tasks->listByProject($projectId)
            );
            $selectedIds = array_map('intval', (array)($_POST['default_task_ids'] ?? []));
            foreach ($selectedIds as $defaultTaskId) {
                $defaultTask = $this->defaultTasks->find($defaultTaskId);
                if (!$defaultTask || (int)$defaultTask['project_type_id'] !== (int)$project['project_type_id']) {
                    continue;
                }
                if (in_array(mb_strtolower(trim((string)$defaultTask['task_name'])), $existingNames, true)) {
                    continue;
                }
                $this->tasks->create($projectId, [
                    'task_name' => $defaultTask['task_name'],
                    'description' => $defaultTask['description'] ?? '',
                    'status' => 'not_started',
                    'priority' => 'medium',
                    'dependency_type' => 'independent',
                    'depends_on_task_id' => null,
                    'assigned_to_person_id' => null,
                    'start_date' => '',
                    'due_date' => '',
                    'parent_task_id' => null,
                ], current_person_id());
            }
        }
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['task_name'] ?? '')) !== '') {
            $data = $this->collect();
            $errors = $this->validateDependency($projectId, null, $data);
            if ($errors) {
                $_SESSION['task_errors'] = $errors;
                header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
                exit;
            }
            $this->tasks->create($projectId, $data, current_person_id());
        }
        header('Location: /index.php?page=project_tasks&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['task_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collect();
            $errors = $this->validateDependency($projectId, $id, $data);
            if ($errors) {
                $_SESSION['task_errors'] = $errors;
                header('Location: /index.php?page=project_tasks&project_id=' . $projectId . '&edit_id=' . $id);
                exit;
            }
            $this->tasks->update($id, $data);
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
            'dependency_type' => ($_POST['dependency_type'] ?? 'independent') === 'dependent' ? 'dependent' : 'independent',
            'depends_on_task_id' => (int)($_POST['depends_on_task_id'] ?? 0) ?: null,
            'assigned_to_person_id' => (int)($_POST['assigned_to_person_id'] ?? 0) ?: null,
            'start_date' => trim((string)($_POST['start_date'] ?? '')),
            'due_date' => trim((string)($_POST['due_date'] ?? '')),
            'parent_task_id' => (int)($_POST['parent_task_id'] ?? 0) ?: null,
        ];
    }

    /**
     * @return string[] validation error messages (empty array = valid)
     */
    private function validateDependency(int $projectId, ?int $taskId, array $data): array
    {
        $errors = [];
        if ($data['dependency_type'] !== 'dependent') {
            return $errors;
        }
        if (empty($data['depends_on_task_id'])) {
            $errors[] = 'Please select which task this one depends on.';
            return $errors;
        }
        $dependsOn = $this->tasks->find((int)$data['depends_on_task_id']);
        if (!$dependsOn || (int)$dependsOn['project_id'] !== $projectId) {
            $errors[] = 'The selected dependency task is invalid.';
            return $errors;
        }
        if ($taskId !== null && $this->tasks->wouldCreateCycle($taskId, (int)$data['depends_on_task_id'])) {
            $errors[] = 'That selection would create a circular dependency between tasks.';
            return $errors;
        }
        if (in_array($data['status'], ['in_progress', 'completed'], true) && $dependsOn['status'] !== 'completed') {
            $errors[] = 'This task depends on "' . $dependsOn['task_name'] . '", which must be completed first.';
        }
        return $errors;
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
