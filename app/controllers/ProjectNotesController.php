<?php
declare(strict_types=1);

final class ProjectNotesController
{
    private PDO $pdo;
    private ProjectNote $notes;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->notes = new ProjectNote($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $noteList = $this->notes->listByProject($projectId);
        $editNote = !empty($_GET['edit_id']) ? $this->notes->find((int)$_GET['edit_id']) : null;
        require APP_ROOT . '/app/views/project_notes/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string)($_POST['notes'] ?? '')) !== '') {
            $this->notes->create($projectId, $this->collect());
        }
        header('Location: /index.php?page=project_notes&project_id=' . $projectId);
        exit;
    }

    public function update(): void
    {
        $id = (int)($_POST['note_id'] ?? 0);
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->notes->update($id, $this->collect());
        }
        header('Location: /index.php?page=project_notes&project_id=' . $projectId);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['note_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->notes->delete($id);
        }
        header('Location: /index.php?page=project_notes&project_id=' . $projectId);
        exit;
    }

    private function collect(): array
    {
        return [
            'person_id' => current_person_id() ?: null,
            'note_date' => trim((string)($_POST['note_date'] ?? '')),
            'notes' => trim((string)($_POST['notes'] ?? '')),
        ];
    }
}
