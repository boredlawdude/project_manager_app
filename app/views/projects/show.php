<?php
declare(strict_types=1);
$pageTitle = $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'details';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
?>

<div class="d-flex justify-content-end mb-3">
    <a href="/index.php?page=projects_edit&project_id=<?= (int)$project['project_id'] ?>" class="btn btn-outline-secondary btn-sm me-2">Edit</a>
    <form method="post" action="/index.php?page=projects_delete&project_id=<?= (int)$project['project_id'] ?>" onsubmit="return confirm('Delete this project? This cannot be undone.');">
        <button type="submit" class="btn btn-outline-danger btn-sm">Delete Project</button>
    </form>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 text-muted">Description</h2>
                <p><?= nl2br(h($project['description'] ?? '')) ?: '<span class="text-muted">No description.</span>' ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-muted">Details</h2>
                <dl class="row mb-0 small">
                    <dt class="col-5">Code</dt><dd class="col-7"><?= h($project['project_code']) ?></dd>
                    <dt class="col-5">Type</dt><dd class="col-7"><?= h($project['project_type_name'] ?? '—') ?></dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><?= h($project['status']) ?></dd>
                    <dt class="col-5">Priority</dt><dd class="col-7"><?= h($project['priority']) ?></dd>
                    <dt class="col-5">Department</dt><dd class="col-7"><?= h($project['department_name'] ?? '—') ?></dd>
                    <dt class="col-5">PM</dt><dd class="col-7"><?= h(trim((string)($project['project_manager_name'] ?? '')) ?: '—') ?></dd>
                    <dt class="col-5">Sponsor</dt><dd class="col-7"><?= h(trim((string)($project['sponsor_name'] ?? '')) ?: '—') ?></dd>
                    <dt class="col-5">Est. Budget</dt><dd class="col-7"><?= h(fmt_money($project['estimated_budget'] ?? null)) ?: '—' ?></dd>
                    <dt class="col-5">Start</dt><dd class="col-7"><?= h(fmt_date($project['start_date'] ?? null)) ?: '—' ?></dd>
                    <dt class="col-5">Target End</dt><dd class="col-7"><?= h(fmt_date($project['target_end_date'] ?? null)) ?: '—' ?></dd>
                    <dt class="col-5">Actual End</dt><dd class="col-7"><?= h(fmt_date($project['actual_end_date'] ?? null)) ?: '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
