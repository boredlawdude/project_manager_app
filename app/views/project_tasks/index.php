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
    <div class="card-header d-flex justify-content-between align-items-center">
        <span id="taskFormTitle"><?= $editTask ? 'Edit Task' : 'Add Task' ?></span>
        <a href="/index.php?page=project_tasks&project_id=<?= $pid ?>" id="taskFormCancelBtn" class="btn btn-outline-secondary btn-sm" style="<?= $editTask ? '' : 'display:none;' ?>">Cancel</a>
    </div>
    <div class="card-body">
        <form method="post" id="taskForm" action="/index.php?page=<?= $editTask ? 'project_tasks_update' : 'project_tasks_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <input type="hidden" name="task_id" id="taskFormTaskId" value="<?= (int)($editTask['task_id'] ?? 0) ?>">
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
                    <label class="form-label small mb-0">Due Date (Projected)</label>
                    <input type="date" name="due_date" class="form-control" value="<?= h($editTask['due_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= h($editTask['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Completed Date</label>
                    <input type="date" name="completed_date" id="completedDateInput" class="form-control"
                           value="<?= h(!empty($editTask['completed_at']) ? substr((string)$editTask['completed_at'], 0, 10) : '') ?>">
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
                <button type="submit" class="btn btn-primary btn-sm" id="taskFormSubmitBtn"><?= $editTask ? 'Save Changes' : 'Add Task' ?></button>
            </div>
        </form>
    </div>
</div>

<?php
$statusBadgeClasses = [
    'completed' => 'text-bg-success',
    'in_progress' => 'text-bg-warning',
    'blocked' => 'text-bg-danger',
];
?>
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
                $isActiveRow = !empty($editTask) && (int)$editTask['task_id'] === (int)$t['task_id'];
            ?>
            <tr class="task-row <?= $isActiveRow ? 'table-active' : '' ?>" data-task-id="<?= (int)$t['task_id'] ?>" style="cursor:pointer;" title="Click to edit this task">
                <td><?= h($t['task_name']) ?></td>
                <td><span class="badge <?= $statusBadgeClasses[$t['status']] ?? 'text-bg-secondary' ?>"><?= h(str_replace('_',' ',$t['status'])) ?></span></td>
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
                <td class="task-due-date-cell" data-due-date="<?= h($t['due_date'] ?? '') ?>"><?= h(fmt_date($t['due_date'] ?? null)) ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_tasks&project_id=<?= $pid ?>&edit_id=<?= (int)$t['task_id'] ?>" class="btn btn-sm btn-outline-secondary task-edit-link" data-task-id="<?= (int)$t['task_id'] ?>">Edit</a>
                    <form method="post" action="/index.php?page=project_tasks_delete&project_id=<?= $pid ?>&task_id=<?= (int)$t['task_id'] ?>" class="d-inline task-delete-form" onsubmit="return confirm('Delete this task?');">
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
    var pid = <?= $pid ?>;
    var typeSel = document.getElementById('dependencyTypeSelect');
    var wrap = document.getElementById('dependsOnWrap');
    var dependsOnSelect = wrap ? wrap.querySelector('select[name="depends_on_task_id"]') : null;

    function toggleDependsOnVisibility() {
        if (typeSel && wrap) {
            wrap.style.display = typeSel.value === 'dependent' ? '' : 'none';
        }
    }
    if (typeSel) {
        typeSel.addEventListener('change', toggleDependsOnVisibility);
        toggleDependsOnVisibility();
    }

    var form = document.getElementById('taskForm');
    var titleEl = document.getElementById('taskFormTitle');
    var cancelBtn = document.getElementById('taskFormCancelBtn');
    var submitBtn = document.getElementById('taskFormSubmitBtn');
    var taskIdField = document.getElementById('taskFormTaskId');
    var fields = {
        task_name: form.querySelector('[name="task_name"]'),
        status: form.querySelector('[name="status"]'),
        priority: form.querySelector('[name="priority"]'),
        assigned_to_person_id: form.querySelector('[name="assigned_to_person_id"]'),
        due_date: form.querySelector('[name="due_date"]'),
        start_date: form.querySelector('[name="start_date"]'),
        completed_date: form.querySelector('[name="completed_date"]'),
        description: form.querySelector('[name="description"]'),
    };

    function todayLocalDate() {
        var now = new Date();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        return now.getFullYear() + '-' + m + '-' + d;
    }

    fields.status.addEventListener('change', function () {
        if (fields.status.value === 'completed') {
            if (!fields.completed_date.value) {
                fields.completed_date.value = todayLocalDate();
            }
        } else {
            fields.completed_date.value = '';
        }
    });

    function highlightRow(taskId) {
        document.querySelectorAll('.task-row').forEach(function (row) {
            row.classList.toggle('table-active', taskId && row.getAttribute('data-task-id') === String(taskId));
        });
    }

    function populateDependsOnOptions(options, selectedId) {
        if (!dependsOnSelect) { return; }
        dependsOnSelect.innerHTML = '';
        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = '— Select task —';
        dependsOnSelect.appendChild(blank);
        options.forEach(function (opt) {
            var o = document.createElement('option');
            o.value = opt.task_id;
            o.textContent = opt.task_name;
            if (selectedId && String(opt.task_id) === String(selectedId)) { o.selected = true; }
            dependsOnSelect.appendChild(o);
        });
    }

    function loadTask(taskId) {
        fetch('/index.php?page=project_tasks_get&project_id=' + pid + '&task_id=' + taskId)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) { return; }
                var t = data.task;
                populateDependsOnOptions(data.dependencyOptions, t ? t.depends_on_task_id : null);

                if (t) {
                    form.action = '/index.php?page=project_tasks_update';
                    taskIdField.value = t.task_id;
                    titleEl.textContent = 'Edit Task';
                    submitBtn.textContent = 'Save Changes';
                    cancelBtn.style.display = '';
                    fields.task_name.value = t.task_name || '';
                    fields.status.value = t.status || 'not_started';
                    fields.priority.value = t.priority || 'medium';
                    fields.assigned_to_person_id.value = t.assigned_to_person_id || '';
                    fields.due_date.value = t.due_date || '';
                    fields.start_date.value = t.start_date || '';
                    fields.completed_date.value = t.completed_at ? String(t.completed_at).substring(0, 10) : '';
                    typeSel.value = t.dependency_type || 'independent';
                    fields.description.value = t.description || '';
                    highlightRow(t.task_id);
                } else {
                    form.action = '/index.php?page=project_tasks_store';
                    taskIdField.value = '';
                    titleEl.textContent = 'Add Task';
                    submitBtn.textContent = 'Add Task';
                    cancelBtn.style.display = 'none';
                    fields.task_name.value = '';
                    fields.status.value = 'not_started';
                    fields.priority.value = 'medium';
                    fields.assigned_to_person_id.value = '';
                    fields.due_date.value = '';
                    fields.start_date.value = '';
                    fields.completed_date.value = '';
                    typeSel.value = 'independent';
                    fields.description.value = '';
                    highlightRow(null);
                }
                toggleDependsOnVisibility();
            });
    }

    document.querySelectorAll('.task-row').forEach(function (row) {
        row.addEventListener('click', function () {
            loadTask(row.getAttribute('data-task-id'));
        });
    });

    document.querySelectorAll('.task-edit-link').forEach(function (link) {
        link.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            loadTask(link.getAttribute('data-task-id'));
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('.task-delete-form').forEach(function (delForm) {
        delForm.addEventListener('click', function (ev) { ev.stopPropagation(); });
    });

    cancelBtn.addEventListener('click', function (ev) {
        ev.preventDefault();
        loadTask(0);
    });
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/gantt_chart.php'; ?>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>

