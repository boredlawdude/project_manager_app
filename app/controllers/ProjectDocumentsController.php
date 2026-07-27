<?php
declare(strict_types=1);

final class ProjectDocumentsController
{
    private PDO $pdo;
    private ProjectDocument $documents;
    private Project $projects;

    public function __construct()
    {
        $this->pdo = db();
        $this->documents = new ProjectDocument($this->pdo);
        $this->projects = new Project($this->pdo);
    }

    public function index(): void
    {
        $projectId = (int)($_GET['project_id'] ?? 0);
        $project = $this->projects->find($projectId);
        if (!$project) { http_response_code(404); echo "Project not found."; return; }

        $documentList = $this->documents->listByProject($projectId);
        require APP_ROOT . '/app/views/project_documents/index.php';
    }

    public function store(): void
    {
        $projectId = (int)($_POST['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['document']['name'])) {
            header('Location: /index.php?page=project_documents&project_id=' . $projectId);
            exit;
        }

        $file = $_FILES['document'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'txt'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt, true)) {
                $dir = storage_path('projects/' . $projectId);
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
                $storedName = uniqid('doc_', true) . '_' . $safeName;
                $dest = $dir . '/' . $storedName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $this->documents->create($projectId, [
                        'doc_type' => trim((string)($_POST['doc_type'] ?? 'other')),
                        'file_name' => $file['name'],
                        'file_path' => 'storage/projects/' . $projectId . '/' . $storedName,
                        'mime_type' => $file['type'] ?? null,
                    ], current_person_id());
                }
            }
        }

        header('Location: /index.php?page=project_documents&project_id=' . $projectId);
        exit;
    }

    public function download(): void
    {
        $id = (int)($_GET['project_document_id'] ?? 0);
        $doc = $this->documents->find($id);
        if (!$doc) { http_response_code(404); echo "Document not found."; return; }

        $path = APP_ROOT . '/' . ltrim($doc['file_path'], '/');
        if (!is_file($path)) { http_response_code(404); echo "File missing."; return; }

        header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($doc['file_name']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function destroy(): void
    {
        $id = (int)($_GET['project_document_id'] ?? 0);
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doc = $this->documents->find($id);
            if ($doc) {
                $path = APP_ROOT . '/' . ltrim($doc['file_path'], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
                $this->documents->delete($id);
            }
        }
        header('Location: /index.php?page=project_documents&project_id=' . $projectId);
        exit;
    }
}
