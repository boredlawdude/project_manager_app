<?php
declare(strict_types=1);
$pageTitle = 'Tasks — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'tasks';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<?php if (!empty($taskErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($taskErrors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($importableDefaultTasks)): ?>
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importDefaultTasksModal">
            Import Default Tasks
        </button>
    </div>

    <div class="modal fade" id="importDefaultTasksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="/index.php?page=project_tasks_import_defaults">
                    <input type="hidden" name="project_id" value="<?= $pid ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Default Tasks</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check mb-2 border-bottom pb-2">
                            <input class="form-check-input" type="checkbox" id="importSelectAll">
                            <label class="form-check-label fw-semibold" for="importSelectAll">Select All</label>
                        </div>
                        <?php foreach ($importableDefaultTasks as $dt): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input import-default-cb" type="checkbox" name="default_task_ids[]"
                                       value="<?= (int)$dt['default_task_id'] ?>" id="importDt<?= (int)$dt['default_task_id'] ?>"
                                       <?= $dt['already_added'] ? 'checked disabled' : '' ?>>
                                <label class="form-check-label" for="importDt<?= (int)$dt['default_task_id'] ?>">
                                    <?= h($dt['task_name']) ?>
                                    <?php if (!empty($dt['description'])): ?><span class="text-muted"> — <?= h($dt['description']) ?></span><?php endif; ?>
                                    <?php if ($dt['already_added']): ?><span class="badge text-bg-secondary ms-1">Already added</span><?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Selected Tasks</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var selectAll = document.getElementById('importSelectAll');
        if (!selectAll) { return; }
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.import-default-cb:not(:disabled)').forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
        });
    })();
    </script>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editTask ? 'Edit Task' : 'Add Task' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editTask ? 'project_tasks_update' : 'project_tasks_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editTask): ?><input type="hidden" name="task_id" value="<?= (int)$editTask['task_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="task_name" class="form-control" placeholder="Task name *" required
                           value="<?= h($editTask['task_name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <?php foreach (['not_started','in_progress','blocked','completed','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($editTask['status'] ?? 'not_started') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?= h($p['priority_name']) ?>" <?= ($editTask['priority'] ?? 'medium') === $p['priority_name'] ? 'selected' : '' ?>><?= h(ucfirst($p['priority_name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to_person_id" class="form-select">
                        <option value="">Unassigned</option>
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int)$person['person_id'] ?>" <?= (string)($editTask['assigned_to_person_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>><?= h($person['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="due_date" class="form-control" value="<?= h($editTask['due_date'] ?? '') ?>" title="Due date">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= h($editTask['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Dependency</label>
                    <select name="dependency_type" id="dependencyTypeSelect" class="form-select">
                        <?php $depType = $editTask['dependency_type'] ?? 'independent'; ?>
                        <option value="independent" <?= $depType === 'independent' ? 'selected' : '' ?>>Independent</option>
                        <option value="dependent" <?= $depType === 'dependent' ? 'selected' : '' ?>>Dependent</option>
                    </select>
                </div>
                <div class="col-md-3" id="dependsOnWrap">
                    <label class="form-label small mb-0">Depends on task</label>
                    <select name="depends_on_task_id" class="form-select">
                        <option value="">— Select task —</option>
                        <?php foreach ($dependencyOptions as $opt): ?>
                            <option value="<?= (int)$opt['task_id'] ?>" <?= (string)($editTask['depends_on_task_id'] ?? '') === (string)$opt['task_id'] ? 'selected' : '' ?>><?= h($opt['task_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <textarea name="description" class="form-control" rows="2" placeholder="Description"><?= h($editTask['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editTask ? 'Save Changes' : 'Add Task' ?></button>
                <?php if ($editTask): ?>
                    <a href="/index.php?page=project_tasks&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Task</th><th>Status</th><th>Priority</th><th>Dependency</th><th>Assignee</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php if (!$taskList): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No tasks yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($taskList as $t): ?>
            <?php
                $isDependent = ($t['dependency_type'] ?? 'independent') === 'dependent';
                $depMet = !$isDependent || empty($t['depends_on_task_id']) || ($t['depends_on_status'] ?? null) === 'completed';
            ?>
            <tr>
                <td><?= h($t['task_name']) ?></td>
                <td><span class="badge text-bg-secondary"><?= h(str_replace('_',' ',$t['status'])) ?></span></td>
                <td><?= h($t['priority']) ?></td>
                <td>
                    <?php if (!$isDependent): ?>
                        <span class="badge text-bg-light text-muted border">Independent</span>
                    <?php else: ?>
                        <span class="badge text-bg-info-subtle text-dark border">Depends on: <?= h($t['depends_on_task_name'] ?? '—') ?></span>
                        <?php if (!$depMet): ?>
                            <span class="badge text-bg-danger">Blocked</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= h(trim((string)($t['assignee_name'] ?? '')) ?: '—') ?></td>
                <td><?= h(fmt_date($t['due_date'] ?? null)) ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_tasks&project_id=<?= $pid ?>&edit_id=<?= (int)$t['task_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_tasks_delete&project_id=<?= $pid ?>&task_id=<?= (int)$t['task_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this task?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
(function () {
    var typeSel = document.getElementById('dependencyTypeSelect');
    var wrap = document.getElementById('dependsOnWrap');
    function toggle() { wrap.style.display = typeSel.value === 'dependent' ? '' : 'none'; }
    if (typeSel && wrap) {
        typeSel.addEventListener('change', toggle);
        toggle();
    }
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>

