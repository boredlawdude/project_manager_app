<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$docId = (int)($_GET['id'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

if ($docId <= 0 || $sig === '') {
    http_response_code(400);
    exit('Bad request.');
}

if (!function_exists('oo_verify') || !oo_verify(['id' => $docId], $sig)) {
    http_response_code(403);
    exit('Forbidden.');
}

$stmt = db()->prepare(
    'SELECT project_document_id, file_name, file_path
     FROM project_documents
     WHERE project_document_id = ?
     LIMIT 1'
);
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    http_response_code(404);
    exit('Not found.');
}

$filePath = trim((string)($doc['file_path'] ?? ''));
if ($filePath === '') {
    http_response_code(404);
    exit('Missing file path.');
}

$abs = str_starts_with($filePath, '/') ? $filePath : APP_ROOT . '/' . ltrim($filePath, '/');
if (!is_file($abs)) {
    http_response_code(404);
    exit('File not found.');
}

$fileName = (string)($doc['file_name'] ?? basename($abs));
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$mimeMap = [
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'odt' => 'application/vnd.oasis.opendocument.text',
    'rtf' => 'application/rtf',
    'txt' => 'text/plain; charset=utf-8',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls' => 'application/vnd.ms-excel',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    'csv' => 'text/csv; charset=utf-8',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'ppt' => 'application/vnd.ms-powerpoint',
    'odp' => 'application/vnd.oasis.opendocument.presentation',
    'pdf' => 'application/pdf',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($abs));
header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '_', $fileName) . '"');
header('X-Content-Type-Options: nosniff');
readfile($abs);
exit;
