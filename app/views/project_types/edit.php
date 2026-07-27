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
            These tasks will be offered as a checklist when someone creates a new project of this type,
            so they can quickly add the standard tasks for this kind of project.
        </p>
        <table class="table table-bordered align-middle">
            <thead>
                <tr><th>Task Name</th><th>Description</th><th style="width:160px">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <form method="post" action="/index.php?page=project_type_default_tasks_update">
                            <input type="hidden" name="default_task_id" value="<?= (int)$task['default_task_id'] ?>">
                            <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
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
                <tr>
                    <form method="post" action="/index.php?page=project_type_default_tasks_store">
                        <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
                        <td><input type="text" name="task_name" class="form-control form-control-sm" placeholder="New task name" required></td>
                        <td><input type="text" name="description" class="form-control form-control-sm" placeholder="Description"></td>
                        <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                    </form>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
