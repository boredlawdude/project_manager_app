<?php
declare(strict_types=1);

final class ProjectsController
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
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'status' => (string)($_GET['status'] ?? ''),
            'department_id' => (int)($_GET['department_id'] ?? 0),
        ];
        $projectList = $this->projects->search($filters);
        $departments = $this->departmentOptions();
        require APP_ROOT . '/app/views/projects/index.php';
    }

    public function create(): void
    {
        $project = $this->emptyProject();
        $departments = $this->departmentOptions();
        $people = $this->peopleOptions();
        $errors = [];
        require APP_ROOT . '/app/views/projects/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?page=projects');
            exit;
        }
        $data = $this->collectFormData();
        $errors = $this->validate($data);

        if ($errors) {
            $project = $data;
            $departments = $this->departmentOptions();
            $people = $this->peopleOptions();
            require APP_ROOT . '/app/views/projects/create.php';
            return;
        }

        $data['created_by_person_id'] = current_person_id();
        $id = $this->projects->create($data);
        header('Location: /index.php?page=projects_show&project_id=' . $id);
        exit;
    }

    public function show(): void
    {
        $id = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($id);
        if (!$project) {
            http_response_code(404);
            echo "Project not found.";
            return;
        }
        require APP_ROOT . '/app/views/projects/show.php';
    }

    public function edit(): void
    {
        $id = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($id);
        if (!$project) {
            http_response_code(404);
            echo "Project not found.";
            return;
        }
        $departments = $this->departmentOptions();
        $people = $this->peopleOptions();
        $errors = [];
        require APP_ROOT . '/app/views/projects/edit.php';
    }

    public function update(): void
    {
        $id = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?page=projects_show&project_id=' . $id);
            exit;
        }
        $data = $this->collectFormData();
        $errors = $this->validate($data);

        if ($errors) {
            $project = array_merge(['project_id' => $id], $data);
            $departments = $this->departmentOptions();
            $people = $this->peopleOptions();
            require APP_ROOT . '/app/views/projects/edit.php';
            return;
        }

        $this->projects->update($id, $data);
        header('Location: /index.php?page=projects_show&project_id=' . $id);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->projects->delete($id);
        }
        header('Location: /index.php?page=projects');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'project_code' => trim((string)($_POST['project_code'] ?? '')),
            'project_name' => trim((string)($_POST['project_name'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'status' => (string)($_POST['status'] ?? 'proposed'),
            'priority' => (string)($_POST['priority'] ?? 'medium'),
            'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
            'project_manager_person_id' => (int)($_POST['project_manager_person_id'] ?? 0) ?: null,
            'sponsor_person_id' => (int)($_POST['sponsor_person_id'] ?? 0) ?: null,
            'start_date' => trim((string)($_POST['start_date'] ?? '')),
            'target_end_date' => trim((string)($_POST['target_end_date'] ?? '')),
            'actual_end_date' => trim((string)($_POST['actual_end_date'] ?? '')),
            'estimated_budget' => trim((string)($_POST['estimated_budget'] ?? '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['project_code'] === '') $errors[] = 'Project code is required.';
        if ($data['project_name'] === '') $errors[] = 'Project name is required.';
        return $errors;
    }

    private function emptyProject(): array
    {
        return [
            'project_code' => '', 'project_name' => '', 'description' => '',
            'status' => 'proposed', 'priority' => 'medium',
            'department_id' => null, 'project_manager_person_id' => null, 'sponsor_person_id' => null,
            'start_date' => '', 'target_end_date' => '', 'actual_end_date' => '', 'estimated_budget' => '',
        ];
    }

    private function departmentOptions(): array
    {
        return $this->pdo->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name")->fetchAll();
    }

    private function peopleOptions(): array
    {
        return $this->pdo->query("SELECT person_id, CONCAT(first_name,' ',last_name) AS name FROM people WHERE is_active = 1 ORDER BY name")->fetchAll();
    }
}
