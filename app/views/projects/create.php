<?php
declare(strict_types=1);
$pageTitle = 'New Project';
require APP_ROOT . '/app/views/layouts/header.php';
?>

<h1 class="h4 mb-3">New Project</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/index.php?page=projects_store" class="card shadow-sm">
    <div class="card-body">
        <?php require APP_ROOT . '/app/views/projects/_form_fields.php'; ?>
    </div>
    <div class="card-footer text-end">
        <a href="/index.php?page=projects" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create Project</button>
    </div>
</form>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
