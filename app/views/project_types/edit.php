<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Project Type</h1>
    <a href="/index.php?page=project_types" class="btn btn-outline-secondary btn-sm">Back to Project Types</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">Saved successfully.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Details</div>
    <div class="card-body">
        <form method="post" action="/index.php?page=project_types_update" class="row g-3">
            <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
            <div class="col-md-5">
                <label class="form-label">Name</label>
                <input type="text" name="project_type_name" class="form-control" value="<?= h($type['project_type_name']) ?>" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Description</label>
                <input type="text" name="project_type_description" class="form-control" value="<?= h($type['project_type_description']) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check me-3">
                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive" <?= $type['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isActive">Active</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Default Tasks</div>
    <div class="card-body">
        <p class="text-muted">
            These tasks will be offered as a checklist when someone creates a new project of this type
            (or later via "Import Default Tasks" on an existing project's Tasks tab), so they can quickly add
            the standard tasks for this kind of project. Drag rows by the <strong>⠿</strong> handle to
            reorder them, then click <strong>Save Order</strong>.
        </p>
        <div class="d-flex align-items-center gap-2 mb-2">
            <button type="button" id="saveTaskOrderBtn" class="btn btn-sm btn-primary">Save Order</button>
            <span id="taskOrderStatus" class="small text-muted"></span>
        </div>
        <table class="table table-bordered align-middle">
            <thead>
                <tr><th style="width:40px"></th><th>Task Name</th><th>Description</th><th style="width:160px">Actions</th></tr>
            </thead>
            <tbody id="defaultTasksSortable">
                <?php foreach ($tasks as $task): ?>
                    <tr draggable="true" data-default-task-id="<?= (int)$task['default_task_id'] ?>" class="default-task-row">
                        <form method="post" action="/index.php?page=project_type_default_tasks_update">
                            <input type="hidden" name="default_task_id" value="<?= (int)$task['default_task_id'] ?>">
                            <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
                            <input type="hidden" name="sort_order" value="<?= (int)$task['sort_order'] ?>">
                            <td class="text-center text-muted drag-handle" style="cursor:grab">⠿</td>
                            <td><input type="text" name="task_name" value="<?= h($task['task_name']) ?>" class="form-control form-control-sm" required></td>
                            <td><input type="text" name="description" value="<?= h($task['description']) ?>" class="form-control form-control-sm"></td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </form>
                        <form method="post" action="/index.php?page=project_type_default_tasks_delete" style="display:inline">
                            <input type="hidden" name="default_task_id" value="<?= (int)$task['default_task_id'] ?>">
                            <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Delete this default task?')">Delete</button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tbody>
                <tr>
                    <form method="post" action="/index.php?page=project_type_default_tasks_store">
                        <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
                        <input type="hidden" name="sort_order" value="<?= (count($tasks) + 1) * 10 ?>">
                        <td></td>
                        <td><input type="text" name="task_name" class="form-control form-control-sm" placeholder="New task name" required></td>
                        <td><input type="text" name="description" class="form-control form-control-sm" placeholder="Description"></td>
                        <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                    </form>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var list = document.getElementById('defaultTasksSortable');
    var saveBtn = document.getElementById('saveTaskOrderBtn');
    var status = document.getElementById('taskOrderStatus');
    var projectTypeId = <?= (int)$type['project_type_id'] ?>;
    var dragEl = null;

    list.addEventListener('dragstart', function (e) {
        var row = e.target.closest('.default-task-row');
        if (!row) { return; }
        dragEl = row;
        e.dataTransfer.effectAllowed = 'move';
        row.classList.add('opacity-50');
    });

    list.addEventListener('dragend', function () {
        if (dragEl) { dragEl.classList.remove('opacity-50'); }
        dragEl = null;
        status.textContent = '';
    });

    list.addEventListener('dragover', function (e) {
        e.preventDefault();
        var row = e.target.closest('.default-task-row');
        if (!row || row === dragEl || !dragEl) { return; }
        var rect = row.getBoundingClientRect();
        var after = (e.clientY - rect.top) > (rect.height / 2);
        list.insertBefore(dragEl, after ? row.nextSibling : row);
        status.textContent = 'Order changed — click Save Order to keep it.';
    });

    saveBtn.addEventListener('click', function () {
        var order = Array.from(list.querySelectorAll('.default-task-row')).map(function (row) {
            return row.getAttribute('data-default-task-id');
        });
        if (!order.length) { return; }
        saveBtn.disabled = true;
        status.textContent = 'Saving…';
        var body = new URLSearchParams();
        body.append('project_type_id', projectTypeId);
        order.forEach(function (id) { body.append('order[]', id); });
        fetch('/index.php?page=project_type_default_tasks_reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                saveBtn.disabled = false;
                status.textContent = data.ok ? 'Order saved.' : 'Failed to save: ' + (data.error || 'unknown error');
            })
            .catch(function () {
                saveBtn.disabled = false;
                status.textContent = 'Failed to save order (network error).';
            });
    });
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
