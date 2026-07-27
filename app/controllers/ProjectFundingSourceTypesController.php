<?php
declare(strict_types=1);

final class ProjectFundingSourceTypesController
{
    private ProjectFundingSourceType $model;

    public function __construct()
    {
        $this->model = new ProjectFundingSourceType(db());
    }

    public function index(): void
    {
        require_system_admin();
        $types = $this->model->all();
        $errors = $_SESSION['pfst_errors'] ?? [];
        $success = $_SESSION['pfst_success'] ?? false;
        unset($_SESSION['pfst_errors'], $_SESSION['pfst_success']);
        require APP_ROOT . '/app/views/admin_settings/project_funding_source_types.php';
    }

    public function store(): void
    {
        require_system_admin();
        $name = trim((string)($_POST['type_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $errors = [];
        if ($name === '') $errors[] = 'Type name is required.';
        if (!$errors) {
            try {
                $this->model->create($name, $desc);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A funding source type named \"$name\" already exists." : 'Failed to create type.';
            }
        }
        $_SESSION['pfst_errors'] = $errors;
        $_SESSION['pfst_success'] = empty($errors);
        header('Location: /index.php?page=admin_funding_source_types');
        exit;
    }

    public function update(): void
    {
        require_system_admin();
        $id = (int)($_POST['funding_source_type_id'] ?? 0);
        $name = trim((string)($_POST['type_name'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $isActive = isset($_POST['is_active']);
        $errors = [];
        if ($id <= 0) $errors[] = 'Invalid type ID.';
        if ($name === '') $errors[] = 'Type name is required.';
        if (!$errors) {
            try {
                $this->model->update($id, $name, $desc, $isActive);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), 'Duplicate') ? "A funding source type named \"$name\" already exists." : 'Failed to update type.';
            }
        }
        $_SESSION['pfst_errors'] = $errors;
        $_SESSION['pfst_success'] = empty($errors);
        header('Location: /index.php?page=admin_funding_source_types');
        exit;
    }

    public function destroy(): void
    {
        require_system_admin();
        $id = (int)($_POST['funding_source_type_id'] ?? 0);
        $errors = [];
        if ($id > 0) {
            try {
                $this->model->delete($id);
            } catch (Throwable $e) {
                $errors[] = str_contains($e->getMessage(), '1451') ? 'Cannot delete: this type is in use.' : 'Delete failed.';
            }
        }
        $_SESSION['pfst_errors'] = $errors;
        $_SESSION['pfst_success'] = empty($errors);
        header('Location: /index.php?page=admin_funding_source_types');
        exit;
    }
}
