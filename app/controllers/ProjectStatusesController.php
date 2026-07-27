<?php
declare(strict_types=1);

final class ProjectStatusesController
{
    private ProjectStatus $model;

    public function __construct()
    {
        $this->model = new ProjectStatus(db());
    }

    public function index(): void
    {
        require_system_admin();
        $statuses = $this->model->all();
        $errors = $_SESSION['ps_errors'] ?? [];
        $success = $_SESSION['ps_success'] ?? false;
        unset($_SESSION['ps_errors'], $_SESSION['ps_success']);
        require APP_ROOT . '/app/views/admin_settings/project_statuses.php';
    }

    public function store(): void
    {
        require_system_admin();
        $name = trim((string)($_POST['status_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $errors = [];
        if ($name === '') $errors[] = 'Status name is required.';
        if (!$errors) {
            try {
                $this->model->create($name, $desc);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A status named \"$name\" already exists." : 'Failed to create status.';
            }
        }
        $_SESSION['ps_errors'] = $errors;
        $_SESSION['ps_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_statuses');
        exit;
    }

    public function update(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_status_id'] ?? 0);
        $name = trim((string)($_POST['status_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $isActive = isset($_POST['is_active']);
        $errors = [];
        if ($id <= 0) $errors[] = 'Invalid status ID.';
        if ($name === '') $errors[] = 'Status name is required.';
        if (!$errors) {
            try {
                $this->model->update($id, $name, $desc, $isActive);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A status named \"$name\" already exists." : 'Failed to update status.';
            }
        }
        $_SESSION['ps_errors'] = $errors;
        $_SESSION['ps_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_statuses');
        exit;
    }

    public function destroy(): void
    {
        require_system_admin();
        $id = (int)($_POST['project_status_id'] ?? 0);
        $errors = [];
        if ($id > 0) {
            try {
                $this->model->delete($id);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), '1451') ? 'Cannot delete: this status is in use by one or more projects.' : 'Delete failed.';
            }
        }
        $_SESSION['ps_errors'] = $errors;
        $_SESSION['ps_success'] = empty($errors);
        header('Location: /index.php?page=admin_project_statuses');
        exit;
    }
}
