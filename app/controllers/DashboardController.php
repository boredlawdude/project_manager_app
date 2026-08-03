<?php
declare(strict_types=1);

final class DashboardController
{
    private PDO $pdo;
    private ProjectTask $tasks;

    public function __construct()
    {
        $this->pdo = db();
        $this->tasks = new ProjectTask($this->pdo);
    }

    public function index(): void
    {
        $person = current_person();
        $personId = (int)($person['person_id'] ?? 0);

        $orgName = '';
        try {
            $orgName = (string)($this->pdo->query('SELECT org_name FROM organization_settings ORDER BY id ASC LIMIT 1')->fetchColumn() ?: '');
        } catch (Throwable $e) {
            $orgName = '';
        }

        $myTasks = $personId > 0 ? $this->tasks->listByAssignee($personId) : [];

        require APP_ROOT . '/app/views/dashboard/index.php';
    }
}
