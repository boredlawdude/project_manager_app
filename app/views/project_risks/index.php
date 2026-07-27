<?php
declare(strict_types=1);
$pageTitle = 'Risks — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'risks';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editRisk ? 'Edit Risk' : 'Add Risk' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editRisk ? 'project_risks_update' : 'project_risks_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editRisk): ?><input type="hidden" name="risk_id" value="<?= (int)$editRisk['risk_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="title" class="form-control" placeholder="Risk title *" required value="<?= h($editRisk['title'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="category" class="form-control" placeholder="Category" value="<?= h($editRisk['category'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <?php foreach (['open','mitigating','closed','realized'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($editRisk['status'] ?? 'open') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Likelihood</label>
                    <select name="likelihood" class="form-select">
                        <?php foreach (['low','medium','high'] as $l): ?>
                            <option value="<?= $l ?>" <?= ($editRisk['likelihood'] ?? 'medium') === $l ? 'selected' : '' ?>><?= ucfirst($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Impact</label>
                    <select name="impact" class="form-select">
                        <?php foreach (['low','medium','high'] as $im): ?>
                            <option value="<?= $im ?>" <?= ($editRisk['impact'] ?? 'medium') === $im ? 'selected' : '' ?>><?= ucfirst($im) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Owner</label>
                    <select name="owner_person_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int)$person['person_id'] ?>" <?= (string)($editRisk['owner_person_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>><?= h($person['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Review Date</label>
                    <input type="date" name="review_date" class="form-control" value="<?= h($editRisk['review_date'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <textarea name="description" class="form-control" rows="2" placeholder="Description"><?= h($editRisk['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <textarea name="mitigation_plan" class="form-control" rows="2" placeholder="Mitigation plan"><?= h($editRisk['mitigation_plan'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editRisk ? 'Save Changes' : 'Add Risk' ?></button>
                <?php if ($editRisk): ?>
                    <a href="/index.php?page=project_risks&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Title</th><th>Category</th><th>Likelihood</th><th>Impact</th><th>Status</th><th>Owner</th><th></th></tr></thead>
        <tbody>
        <?php if (!$riskList): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No risks logged yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($riskList as $r): ?>
            <tr>
                <td><?= h($r['title']) ?></td>
                <td><?= h($r['category'] ?? '') ?></td>
                <td><?= h(ucfirst($r['likelihood'])) ?></td>
                <td><?= h(ucfirst($r['impact'])) ?></td>
                <td><span class="badge text-bg-secondary"><?= h(ucfirst($r['status'])) ?></span></td>
                <td><?= h(trim((string)($r['owner_name'] ?? '')) ?: '—') ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_risks&project_id=<?= $pid ?>&edit_id=<?= (int)$r['risk_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_risks_delete&project_id=<?= $pid ?>&risk_id=<?= (int)$r['risk_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this risk?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
