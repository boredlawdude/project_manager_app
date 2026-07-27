<?php
declare(strict_types=1);

final class ProjectPrioritiesController
{
    private ProjectPriority $model;

    public function __construct()
    {
        $this->model = new ProjectPriority(db());
    }

    public function index(): void
    {
        require_system_admin();
        $priorities = $this->model->all();
        $errors = $_SESSION['pp_errors'] ?? [];
        $success = $_SESSION['pp_success'] ?? false;
        unset($_SESSION['pp_errors'], $_SESSION['pp_success']);
        require APP_ROOT . '/app/views/admin_settings/project_priorities.php';
    }

    public function store(): void
    {
        require_system_admin();
        $name = trim((string)($_POST['priority_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $errors = [];
        if ($name === '') $errors[] = 'Priority name is required.';
        if (!$errors) {
            try {
                $this->model->create($name, $desc);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A priority named \"$name\" already exists." : 'Failed to create priority.';
            }
        }
        $_SESSION['pp_errors'] = $errors;
        $_SESSION['pp_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_priorities');
        exit;
    }

    public function update(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_priority_id'] ?? 0);
        $name = trim((string)($_POST['priority_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $isActive = isset($_POST['is_active']);
        $errors = [];
        if ($id <= 0) $errors[] = 'Invalid priority ID.';
        if ($name === '') $errors[] = 'Priority name is required.';
        if (!$errors) {
            try {
                $this->model->update($id, $name, $desc, $isActive);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A priority named \"$name\" already exists." : 'Failed to update priority.';
            }
        }
        $_SESSION['pp_errors'] = $errors;
        $_SESSION['pp_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_priorities');
        exit;
    }

    public function destroy(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_priority_id'] ?? 0);
        $errors = [];
        if ($id > 0) {
            try {
                $this->model->delete($id);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), '1451') ? 'Cannot delete: this priority is in use.' : 'Delete failed.';
            }
        }
        $_SESSION['pp_errors'] = $errors;
        $_SESSION['pp_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_priorities');
        exit;
    }
}
