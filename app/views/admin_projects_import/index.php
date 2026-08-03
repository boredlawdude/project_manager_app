<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Import Projects from CSV</h1>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">&larr; Back to Admin Settings</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <p class="text-muted">
            Upload a "Project Manager" style CSV export to bulk-create projects. At minimum this maps
            project name, project number, description, and project manager. Project type, department,
            priority, status, budget, and schedule dates are mapped where possible.
        </p>
        <p class="text-muted">
            Re-uploading the same file is safe — rows whose project number was already imported are
            skipped. Always run <strong>Preview (dry run)</strong> first and review the results below
            before choosing <strong>Import for real</strong>.
        </p>
        <form method="post" action="/index.php?page=admin_projects_import_upload" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold">CSV File</label>
                <input type="file" class="form-control" name="csv_file" accept=".csv" required>
            </div>
            <button type="submit" name="dry_run" value="1" class="btn btn-outline-primary">Preview (dry run)</button>
            <button type="submit" name="dry_run" value="0" class="btn btn-primary"
                    onclick="return confirm('This will insert projects into the database. Continue?');">
                Import for real
            </button>
        </form>
    </div>
</div>

<?php if ($result): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <?= !empty($result['dry_run']) ? 'Preview Results (nothing was saved)' : 'Import Results' ?>
        </div>
        <div class="card-body">
            <p class="mb-2">
                <span class="badge text-bg-success">Inserted: <?= (int)($result['inserted'] ?? 0) ?></span>
                <span class="badge text-bg-secondary">Skipped: <?= (int)($result['skipped'] ?? 0) ?></span>
            </p>
            <pre class="small bg-light p-3 border rounded" style="max-height: 480px; overflow-y: auto;"><?= h(implode("\n", $result['log'] ?? [])) ?></pre>
        </div>
    </div>
<?php endif; ?>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
