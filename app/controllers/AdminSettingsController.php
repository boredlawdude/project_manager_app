<?php
declare(strict_types=1);

final class AdminSettingsController
{
    private PmSetting $settings;

    public function __construct()
    {
        $this->settings = new PmSetting(db());
    }

    public function index(): void
    {
        require_system_admin();
        $settings = $this->settings->all();
        $errors = $_SESSION['admin_settings_errors'] ?? [];
        $success = $_SESSION['admin_settings_success'] ?? false;
        unset($_SESSION['admin_settings_errors'], $_SESSION['admin_settings_success']);
        require APP_ROOT . '/app/views/admin_settings/index.php';
    }

    public function update(): void
    {
        require_system_admin();
        $path = trim((string)($_POST['document_root_path'] ?? ''));
        $errors = [];
        if ($path !== '' && (!str_starts_with($path, '/') || !is_dir($path))) {
            $errors[] = 'Document root path must be a valid absolute directory path that exists on the server.';
        }
        if (!$errors) {
            $this->settings->set('document_root_path', $path);
        }
        $_SESSION['admin_settings_errors'] = $errors;
        $_SESSION['admin_settings_success'] = empty($errors);
        header('Location: /index.php?page=admin_settings');
        exit;
    }
}
