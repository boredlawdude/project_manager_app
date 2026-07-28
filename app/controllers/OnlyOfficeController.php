<?php
declare(strict_types=1);

final class OnlyOfficeController
{
    private const TYPE_MAP = [
        'docx' => 'word', 'doc' => 'word', 'odt' => 'word', 'rtf' => 'word', 'txt' => 'word',
        'xlsx' => 'cell', 'xls' => 'cell', 'ods' => 'cell', 'csv' => 'cell',
        'pptx' => 'slide', 'ppt' => 'slide', 'odp' => 'slide',
        'pdf' => 'pdf',
    ];

    private PDO $pdo;
    private ProjectDocument $documents;

    public function __construct()
    {
        $this->pdo = db();
        $this->documents = new ProjectDocument($this->pdo);
    }

    public function editor(): void
    {
        $docId = (int)($_GET['document_id'] ?? 0);
        if ($docId <= 0) {
            http_response_code(400);
            echo 'Invalid document id.';
            return;
        }

        $doc = $this->documents->find($docId);
        if (!$doc) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }

        $projectId = (int)($doc['project_id'] ?? 0);
        if ($projectId <= 0) {
            http_response_code(404);
            echo 'Project not found.';
            return;
        }

        $fileName = (string)($doc['file_name'] ?? '');
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!isset(self::TYPE_MAP[$ext])) {
            http_response_code(400);
            echo 'Inline editor does not support this file type.';
            return;
        }

        $appBaseUrl = rtrim($this->appBaseUrl(), '/');
        $docSig = oo_sign(['id' => $docId]);
        $cbSig = oo_sign(['doc_id' => $docId]);
        $fsSig = oo_sign(['doc_id' => $docId, 'action' => 'forcesave']);

        $docUrl = $appBaseUrl . '/onlyoffice_download.php?id=' . $docId . '&sig=' . urlencode($docSig);
        $callbackUrl = $appBaseUrl . '/onlyoffice_callback.php?doc_id=' . $docId . '&sig=' . urlencode($cbSig);
        $forceSaveUrl = $appBaseUrl . '/onlyoffice_forcesave.php?doc_id=' . $docId . '&sig=' . urlencode($fsSig);

        $mtime = $this->documentMtime($doc);
        $keyMaterial = $docId . '|' . ((string)$doc['file_path']) . '|' . (string)$mtime;
        $docKey = substr(hash('sha256', $keyMaterial), 0, 64);

        $person = current_person();
        $userName = trim((string)($person['name'] ?? $person['email'] ?? 'User'));

        $editorConfig = [
            'document' => [
                'fileType' => $ext,
                'key' => $docKey,
                'title' => $fileName !== '' ? $fileName : ('Document_' . $docId . '.' . $ext),
                'url' => $docUrl,
            ],
            'documentType' => self::TYPE_MAP[$ext],
            'editorConfig' => [
                'mode' => 'edit',
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => (string)current_person_id(),
                    'name' => $userName,
                ],
                'customization' => [
                    'forcesave' => true,
                    'zoom' => -2,
                ],
            ],
        ];

        $jwtSecret = trim((string)($_ENV['ONLYOFFICE_JWT_SECRET'] ?? ''));
        $editorToken = null;
        if ($jwtSecret !== '' && function_exists('oo_jwt_sign')) {
            $editorToken = oo_jwt_sign($editorConfig, $jwtSecret);
        }

        $documentServerUrl = rtrim((string)($_ENV['ONLYOFFICE_DOCUMENT_SERVER_URL'] ?? ''), '/');
        if ($documentServerUrl === '') {
            http_response_code(500);
            echo 'Missing ONLYOFFICE_DOCUMENT_SERVER_URL in environment.';
            return;
        }

        require APP_ROOT . '/app/views/project_documents/onlyoffice_editor.php';
    }

    private function documentMtime(array $doc): int
    {
        $path = (string)($doc['file_path'] ?? '');
        if ($path === '') {
            return time();
        }

        $abs = $this->resolveDocPath($path);
        $mtime = @filemtime($abs);
        return $mtime !== false ? (int)$mtime : time();
    }

    /** file_path is stored absolute for new uploads; older rows may have a path relative to APP_ROOT. */
    private function resolveDocPath(string $filePath): string
    {
        return str_starts_with($filePath, '/') ? $filePath : APP_ROOT . '/' . ltrim($filePath, '/');
    }

    private function appBaseUrl(): string
    {
        $explicit = trim((string)($_ENV['ONLYOFFICE_APP_BASE_URL'] ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }

        $appUrl = trim((string)($_ENV['APP_URL'] ?? ''));
        if ($appUrl !== '') {
            return $appUrl;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host;
    }
}
