<?php
declare(strict_types=1);
$pageTitle = 'Tasks — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'tasks';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

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
        <thead><tr><th>Task</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php if (!$taskList): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No tasks yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($taskList as $t): ?>
            <tr>
                <td><?= h($t['task_name']) ?></td>
                <td><span class="badge text-bg-secondary"><?= h(str_replace('_',' ',$t['status'])) ?></span></td>
                <td><?= h($t['priority']) ?></td>
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

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
