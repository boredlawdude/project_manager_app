<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Project Types</h1>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">Back to Admin Settings</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="post" action="/index.php?page=project_types_store" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="project_type_name" class="form-control" placeholder="New project type name" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="project_type_description" class="form-control" placeholder="Description">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Add Project Type</button>
            </div>
        </form>
    </div>
</div>

<table class="table table-bordered align-middle bg-white">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Description</th><th style="width:90px">Active</th><th style="width:180px">Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($types as $type): ?>
            <tr>
                <td><?= (int)$type['project_type_id'] ?></td>
                <td><?= h($type['project_type_name']) ?></td>
                <td><?= h($type['project_type_description']) ?></td>
                <td class="text-center"><?= $type['is_active'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="/index.php?page=project_types_edit&project_type_id=<?= (int)$type['project_type_id'] ?>" class="btn btn-sm btn-primary">Edit / Default Tasks</a>
                    <form method="post" action="/index.php?page=project_types_delete" style="display:inline">
                        <input type="hidden" name="project_type_id" value="<?= (int)$type['project_type_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Delete this project type?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
