<?php
declare(strict_types=1);
$pageTitle = 'Funding — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'funding';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editFunding ? 'Edit Funding Source' : 'Add Funding Source' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editFunding ? 'project_funding_update' : 'project_funding_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editFunding): ?><input type="hidden" name="funding_source_id" value="<?= (int)$editFunding['funding_source_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="source_name" class="form-control" placeholder="Source name *" required value="<?= h($editFunding['source_name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="source_type" class="form-select">
                        <?php foreach ($fundingSourceTypes as $t): ?>
                            <option value="<?= h($t['type_name']) ?>" <?= ($editFunding['source_type'] ?? 'other') === $t['type_name'] ? 'selected' : '' ?>><?= h(ucwords(str_replace('_',' ',$t['type_name']))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="grant_number" class="form-control" placeholder="Grant #" value="<?= h($editFunding['grant_number'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <?php foreach (['anticipated','awarded','received','closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($editFunding['status'] ?? 'anticipated') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="expiration_date" class="form-control" value="<?= h($editFunding['expiration_date'] ?? '') ?>" title="Expiration date">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Awarded Amount</label>
                    <input type="number" step="0.01" name="awarded_amount" class="form-control" value="<?= h($editFunding['awarded_amount'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Received Amount</label>
                    <input type="number" step="0.01" name="received_amount" class="form-control" value="<?= h($editFunding['received_amount'] ?? '0') ?>">
                </div>
                <div class="col-md-6">
                    <textarea name="notes" class="form-control" rows="2" placeholder="Notes"><?= h($editFunding['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editFunding ? 'Save Changes' : 'Add Funding Source' ?></button>
                <?php if ($editFunding): ?>
                    <a href="/index.php?page=project_funding&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Source</th><th>Type</th><th>Grant #</th><th>Awarded</th><th>Received</th><th>Status</th><th>Expires</th><th></th></tr></thead>
        <tbody>
        <?php if (!$fundingList): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No funding sources yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($fundingList as $f): ?>
            <tr>
                <td><?= h($f['source_name']) ?></td>
                <td><?= h(ucwords(str_replace('_',' ',$f['source_type']))) ?></td>
                <td><?= h($f['grant_number'] ?? '') ?></td>
                <td><?= h(fmt_money($f['awarded_amount'])) ?></td>
                <td><?= h(fmt_money($f['received_amount'])) ?></td>
                <td><span class="badge text-bg-secondary"><?= h(ucfirst($f['status'])) ?></span></td>
                <td><?= h(fmt_date($f['expiration_date'] ?? null)) ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_funding&project_id=<?= $pid ?>&edit_id=<?= (int)$f['funding_source_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_funding_delete&project_id=<?= $pid ?>&funding_source_id=<?= (int)$f['funding_source_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this funding source?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
