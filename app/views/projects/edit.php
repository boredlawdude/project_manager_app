<?php
declare(strict_types=1);
$pageTitle = 'Edit Project';
require APP_ROOT . '/app/views/layouts/header.php';
?>

<h1 class="h4 mb-3">Edit Project</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/index.php?page=projects_update&project_id=<?= (int)$project['project_id'] ?>" class="card shadow-sm">
    <div class="card-body">
        <?php require APP_ROOT . '/app/views/projects/_form_fields.php'; ?>
    </div>
    <div class="card-footer text-end">
        <a href="/index.php?page=projects_show&project_id=<?= (int)$project['project_id'] ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</form>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
