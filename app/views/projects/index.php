<?php
declare(strict_types=1);
$pageTitle = 'Projects';
require APP_ROOT . '/app/views/layouts/header.php';
?>

<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-auto">Projects</h1>
    <a href="/index.php?page=projects_create" class="btn btn-primary">+ New Project</a>
</div>

<form method="get" action="/index.php" class="card shadow-sm mb-3">
    <input type="hidden" name="page" value="projects">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-control" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Code, name, description">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <?php foreach (['proposed','active','on_hold','completed','cancelled'] as $s): ?>
                    <option value="<?= h($s) ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_',' ',$s))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= (int)$d['department_id'] ?>" <?= (string)($_GET['department_id'] ?? '') === (string)$d['department_id'] ? 'selected' : '' ?>>
                        <?= h($d['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Department</th>
                <th>Project Manager</th>
                <th>Target End</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$projectList): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No projects found.</td></tr>
            <?php endif; ?>
            <?php foreach ($projectList as $p): ?>
                <tr>
                    <td><a href="/index.php?page=projects_show&project_id=<?= (int)$p['project_id'] ?>"><?= h($p['project_code']) ?></a></td>
                    <td><?= h($p['project_name']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= h($p['status']) ?></span></td>
                    <td><?= h($p['priority']) ?></td>
                    <td><?= h($p['department_name'] ?? '') ?></td>
                    <td><?= h(trim((string)($p['project_manager_name'] ?? '')) ?: '—') ?></td>
                    <td><?= h(fmt_date($p['target_end_date'] ?? null)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
