<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Admin Settings</h1>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">Settings saved.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Dropdown Lists</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="/index.php?page=admin_project_statuses" class="btn btn-outline-primary w-100">Project Statuses</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/index.php?page=admin_project_priorities" class="btn btn-outline-primary w-100">Priorities</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/index.php?page=admin_funding_source_types" class="btn btn-outline-primary w-100">Funding Source Types</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/index.php?page=admin_document_types" class="btn btn-outline-primary w-100">Document Types</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="/index.php?page=project_types" class="btn btn-outline-primary w-100">Project Types &amp; Default Tasks</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Data Import</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="/index.php?page=admin_projects_import" class="btn btn-outline-primary w-100">Import Projects from CSV</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Storage</div>
    <div class="card-body">
        <form method="post" action="/index.php?page=admin_settings_update">
            <label class="form-label fw-bold">Document Root Path</label>
            <div class="form-text text-muted mb-2">
                Absolute directory path on the server where uploaded project documents are stored.
                Leave blank to use the app default (<code>storage/projects</code> inside the app folder).
            </div>
            <input type="text" class="form-control font-monospace" name="document_root_path"
                   placeholder="/var/www/project_manager_app/storage/projects"
                   value="<?= h($settings['document_root_path']['setting_value'] ?? '') ?>">
            <button type="submit" class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
