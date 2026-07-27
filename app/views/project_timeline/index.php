<?php
declare(strict_types=1);
$pageTitle = 'Timeline — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'timeline';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editMilestone ? 'Edit Milestone' : 'Add Milestone' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editMilestone ? 'project_timeline_update' : 'project_timeline_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editMilestone): ?><input type="hidden" name="milestone_id" value="<?= (int)$editMilestone['milestone_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="milestone_name" class="form-control" placeholder="Milestone name *" required value="<?= h($editMilestone['milestone_name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Target Date</label>
                    <input type="date" name="target_date" class="form-control" value="<?= h($editMilestone['target_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Actual Date</label>
                    <input type="date" name="actual_date" class="form-control" value="<?= h($editMilestone['actual_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <?php foreach (['pending','on_track','at_risk','delayed','completed'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($editMilestone['status'] ?? 'pending') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="sort_order" class="form-control" placeholder="Sort order" value="<?= h($editMilestone['sort_order'] ?? '0') ?>">
                </div>
                <div class="col-12">
                    <textarea name="description" class="form-control" rows="2" placeholder="Description"><?= h($editMilestone['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editMilestone ? 'Save Changes' : 'Add Milestone' ?></button>
                <?php if ($editMilestone): ?>
                    <a href="/index.php?page=project_timeline&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Milestone</th><th>Target</th><th>Actual</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (!$milestoneList): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No milestones yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($milestoneList as $m): ?>
            <tr>
                <td><?= h($m['milestone_name']) ?></td>
                <td><?= h(fmt_date($m['target_date'] ?? null)) ?></td>
                <td><?= h(fmt_date($m['actual_date'] ?? null)) ?></td>
                <td><span class="badge text-bg-secondary"><?= h(ucwords(str_replace('_',' ',$m['status']))) ?></span></td>
                <td class="text-end">
                    <a href="/index.php?page=project_timeline&project_id=<?= $pid ?>&edit_id=<?= (int)$m['milestone_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_timeline_delete&project_id=<?= $pid ?>&milestone_id=<?= (int)$m['milestone_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this milestone?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
