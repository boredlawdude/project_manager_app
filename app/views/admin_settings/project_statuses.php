<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Project Statuses</h1>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">Back to Admin Settings</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">Saved successfully.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<table class="table table-bordered align-middle bg-white">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Description</th><th style="width:90px">Active</th><th style="width:140px">Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($statuses as $status): ?>
            <tr>
                <form method="post" action="/index.php?page=admin_project_statuses_update">
                    <input type="hidden" name="project_status_id" value="<?= (int)$status['project_status_id'] ?>">
                    <td><?= (int)$status['project_status_id'] ?></td>
                    <td><input type="text" name="status_name" value="<?= h($status['status_name']) ?>" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="description" value="<?= h($status['description']) ?>" class="form-control form-control-sm"></td>
                    <td class="text-center"><input type="checkbox" name="is_active" class="form-check-input" <?= $status['is_active'] ? 'checked' : '' ?>></td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </form>
                <form method="post" action="/index.php?page=admin_project_statuses_delete" style="display:inline">
                    <input type="hidden" name="project_status_id" value="<?= (int)$status['project_status_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Delete this status?')">Delete</button>
                </form>
                    </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <form method="post" action="/index.php?page=admin_project_statuses_store">
                <td></td>
                <td><input type="text" name="status_name" class="form-control form-control-sm" placeholder="New status name" required></td>
                <td><input type="text" name="description" class="form-control form-control-sm" placeholder="Description"></td>
                <td></td>
                <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
            </form>
        </tr>
    </tbody>
</table>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
