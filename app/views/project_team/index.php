<?php
declare(strict_types=1);
$pageTitle = 'Team — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'team';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header">Add Team Member</div>
    <div class="card-body">
        <form method="post" action="/index.php?page=project_team_store">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-0">Person *</label>
                    <select name="person_id" class="form-select" required>
                        <option value="">Select a person</option>
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int)$person['person_id'] ?>"><?= h($person['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Role on Project</label>
                    <input type="text" name="project_role" class="form-control" placeholder="e.g. Engineer, Contract Admin">
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input type="checkbox" name="is_lead" value="1" class="form-check-input" id="isLeadCheck">
                        <label class="form-check-label" for="isLeadCheck">Lead</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Name</th><th>Role</th><th>Lead</th><th>Email</th><th></th></tr></thead>
        <tbody>
        <?php if (!$memberList): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No team members added yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($memberList as $m): ?>
            <tr>
                <td><?= h($m['person_name']) ?></td>
                <td><?= h($m['project_role'] ?? '') ?></td>
                <td><?= $m['is_lead'] ? '<span class="badge text-bg-primary">Lead</span>' : '' ?></td>
                <td><?= h($m['email'] ?? '') ?></td>
                <td class="text-end">
                    <form method="post" action="/index.php?page=project_team_delete&project_id=<?= $pid ?>&person_id=<?= (int)$m['person_id'] ?>" class="d-inline" onsubmit="return confirm('Remove this team member?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
