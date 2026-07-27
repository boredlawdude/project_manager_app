<?php
declare(strict_types=1);
$pageTitle = 'Documents — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'documents';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header">Upload Document</div>
    <div class="card-body">
        <form method="post" action="/index.php?page=project_documents_store" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-0">Document Type</label>
                    <select name="doc_type" class="form-select">
                        <?php foreach ($documentTypes as $t): ?>
                            <option value="<?= h($t['type_name']) ?>"><?= h(ucwords(str_replace('_',' ',$t['type_name']))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="file" name="document" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>File</th><th>Type</th><th>Uploaded By</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if (!$documentList): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($documentList as $d): ?>
            <tr>
                <td><a href="/index.php?page=project_documents_download&project_document_id=<?= (int)$d['project_document_id'] ?>"><?= h($d['file_name']) ?></a></td>
                <td><?= h(ucfirst($d['doc_type'])) ?></td>
                <td><?= h(trim((string)($d['uploaded_by_name'] ?? '')) ?: '—') ?></td>
                <td><?= h(fmt_date($d['created_at'] ?? null)) ?></td>
                <td class="text-end">
                    <form method="post" action="/index.php?page=project_documents_delete&project_id=<?= $pid ?>&project_document_id=<?= (int)$d['project_document_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this document?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
