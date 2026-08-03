<?php
declare(strict_types=1);

final class AdminProjectImportController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function index(): void
    {
        require_system_admin();
        $result = $_SESSION['admin_import_result'] ?? null;
        unset($_SESSION['admin_import_result']);
        require APP_ROOT . '/app/views/admin_projects_import/index.php';
    }

    public function upload(): void
    {
        require_system_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?page=admin_projects_import');
            exit;
        }

        $dryRun = !empty($_POST['dry_run']);
        $file = $_FILES['csv_file'] ?? null;

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['admin_import_result'] = [
                'log' => ['No CSV file was uploaded, or the upload failed.'],
                'inserted' => 0,
                'skipped' => 0,
                'dry_run' => $dryRun,
            ];
            header('Location: /index.php?page=admin_projects_import');
            exit;
        }

        if (strtolower((string)pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
            $_SESSION['admin_import_result'] = [
                'log' => ['Please upload a .csv file.'],
                'inserted' => 0,
                'skipped' => 0,
                'dry_run' => $dryRun,
            ];
            header('Location: /index.php?page=admin_projects_import');
            exit;
        }

        // is_uploaded_file() requires the real upload tmp path.
        if (!is_uploaded_file($file['tmp_name'])) {
            $_SESSION['admin_import_result'] = [
                'log' => ['Upload validation failed.'],
                'inserted' => 0,
                'skipped' => 0,
                'dry_run' => $dryRun,
            ];
            header('Location: /index.php?page=admin_projects_import');
            exit;
        }

        require_once APP_ROOT . '/app/models/ProjectCsvImporter.php';

        try {
            $importer = new ProjectCsvImporter($this->pdo);
            $result = $importer->run($file['tmp_name'], $dryRun);
            $result['dry_run'] = $dryRun;
        } catch (Throwable $e) {
            $result = [
                'log' => ['Import failed: ' . $e->getMessage()],
                'inserted' => 0,
                'skipped' => 0,
                'dry_run' => $dryRun,
            ];
        }

        $_SESSION['admin_import_result'] = $result;
        header('Location: /index.php?page=admin_projects_import');
        exit;
    }
}
