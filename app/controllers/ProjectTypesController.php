<?php
declare(strict_types=1);

final class ProjectTypesController
{
    private ProjectType $model;
    private ProjectTypeDefaultTask $defaultTasks;

    public function __construct()
    {
        $this->model = new ProjectType(db());
        $this->defaultTasks = new ProjectTypeDefaultTask(db());
    }

    public function index(): void
    {
        require_system_admin();
        $types = $this->model->all();
        $errors = $_SESSION['pt_errors'] ?? [];
        $success = $_SESSION['pt_success'] ?? false;
        unset($_SESSION['pt_errors'], $_SESSION['pt_success']);
        require APP_ROOT . '/app/views/project_types/index.php';
    }

    public function store(): void
    {
        require_system_admin();
        $name = trim((string)($_POST['project_type_name'] ?? ''));
        $desc = trim((string)($_POST['project_type_description'] ?? ''));
        $errors = [];
        if ($name === '') $errors[] = 'Project type name is required.';
        if (!$errors) {
            try {
                $newId = $this->model->create($name, $desc);
                header('Location: /index.php?page=project_types_edit&project_type_id=' . $newId);
                exit;
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A project type named \"$name\" already exists." : 'Failed to create project type.';
            }
        }
        $_SESSION['pt_errors'] = $errors;
        $_SESSION['pt_success'] = false;
        header('Location: /index.php?page=project_types');
        exit;
    }

    public function edit(): void
    {
        require_system_admin();
        $id = (int)($_GET['project_type_id'] ?? 0);
        $type = $this->model->find($id);
        if (!$type) {
            http_response_code(404);
            echo "Project type not found.";
            return;
        }
        $tasks = $this->defaultTasks->listByType($id);
        $errors = $_SESSION['pt_errors'] ?? [];
        $success = $_SESSION['pt_success'] ?? false;
        unset($_SESSION['pt_errors'], $_SESSION['pt_success']);
        require APP_ROOT . '/app/views/project_types/edit.php';
    }

    public function update(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_type_id'] ?? 0);
        $name = trim((string)($_POST['project_type_name'] ?? ''));
        $desc = trim((string)($_POST['project_type_description'] ?? ''));
        $isActive = isset($_POST['is_active']);
        $errors = [];
        if ($id <= 0) $errors[] = 'Invalid project type ID.';
        if ($name === '') $errors[] = 'Project type name is required.';
        if (!$errors) {
            try {
                $this->model->update($id, $name, $desc, $isActive);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A project type named \"$name\" already exists." : 'Failed to update project type.';
            }
        }
        $_SESSION['pt_errors'] = $errors;
        $_SESSION['pt_success'] = empty($errors);
        header('Location: /index.php?page=project_types_edit&project_type_id=' . $id);
        exit;
    }

    public function destroy(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_type_id'] ?? 0);
        $errors = [];
        if ($id > 0) {
            try {
                $this->model->delete($id);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), '1451') ? 'Cannot delete: this project type is in use by one or more projects.' : 'Delete failed.';
            }
        }
        $_SESSION['pt_errors'] = $errors;
        $_SESSION['pt_success'] = empty($errors);
        header('Location: /index.php?page=project_types');
        exit;
    }

    // ── Default tasks nested under a project type ──────────────────────

    public function defaultTaskStore(): void
    {
        require_system_admin();
        $typeId = (int)($_POST['project_type_id'] ?? 0);
        $taskName = trim((string)($_POST['task_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        if ($typeId > 0 && $taskName !== '') {
            $this->defaultTasks->create($typeId, $taskName, $desc);
        }
        header('Location: /index.php?page=project_types_edit&project_type_id=' . $typeId);
        exit;
    }

    public function defaultTaskUpdate(): void
    {
        require_system_admin();
        $id = (int)($_POST['default_task_id'] ?? 0);
        $typeId = (int)($_POST['project_type_id'] ?? 0);
        $taskName = trim((string)($_POST['task_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        if ($id > 0 && $taskName !== '') {
            $this->defaultTasks->update($id, $taskName, $desc);
        }
        header('Location: /index.php?page=project_types_edit&project_type_id=' . $typeId);
        exit;
    }

    public function defaultTaskDestroy(): void
    {
        require_system_admin();
        $id = (int)($_POST['default_task_id'] ?? 0);
        $typeId = (int)($_POST['project_type_id'] ?? 0);
        if ($id > 0) {
            $this->defaultTasks->delete($id);
        }
        header('Location: /index.php?page=project_types_edit&project_type_id=' . $typeId);
        exit;
    }
}
