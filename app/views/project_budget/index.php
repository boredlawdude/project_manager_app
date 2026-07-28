<?php
declare(strict_types=1);
$pageTitle = 'Budget — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'budget';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total Budgeted</div>
            <div class="h4 mb-0"><?= h(fmt_money($totals['budgeted'])) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total Committed</div>
            <div class="h4 mb-0"><?= h(fmt_money($totals['committed'])) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total Actual</div>
            <div class="h4 mb-0"><?= h(fmt_money($totals['actual'])) ?></div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editLine ? 'Edit Budget Line' : 'Add Budget Line' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editLine ? 'project_budget_update' : 'project_budget_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editLine): ?><input type="hidden" name="budget_line_id" value="<?= (int)$editLine['budget_line_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="line_name" class="form-control" placeholder="Line item name *" required value="<?= h($editLine['line_name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <input type="text" name="category" class="form-control" placeholder="Category" value="<?= h($editLine['category'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" name="fiscal_year" class="form-control" placeholder="FY" value="<?= h($editLine['fiscal_year'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <select name="funding_source_id" class="form-select">
                        <?php if ($fundingOptions): ?>
                            <option value="">Funding source</option>
                            <?php foreach ($fundingOptions as $f): ?>
                                <option value="<?= (int)$f['funding_source_id'] ?>" <?= (string)($editLine['funding_source_id'] ?? '') === (string)$f['funding_source_id'] ? 'selected' : '' ?>><?= h($f['source_name']) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled selected>No funding sources yet — add one in the Funding tab</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Budgeted</label>
                    <input type="number" step="0.01" name="budgeted_amount" class="form-control" value="<?= h($editLine['budgeted_amount'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Committed</label>
                    <input type="number" step="0.01" name="committed_amount" class="form-control" value="<?= h($editLine['committed_amount'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-0">Actual</label>
                    <input type="number" step="0.01" name="actual_amount" class="form-control" value="<?= h($editLine['actual_amount'] ?? '0') ?>">
                </div>
                <div class="col-12">
                    <textarea name="notes" class="form-control" rows="2" placeholder="Notes"><?= h($editLine['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editLine ? 'Save Changes' : 'Add Line' ?></button>
                <?php if ($editLine): ?>
                    <a href="/index.php?page=project_budget&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Line</th><th>Category</th><th>FY</th><th>Budgeted</th><th>Committed</th><th>Actual</th><th>Funding</th><th></th></tr></thead>
        <tbody>
        <?php if (!$budgetList): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No budget lines yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($budgetList as $b): ?>
            <tr>
                <td><?= h($b['line_name']) ?></td>
                <td><?= h($b['category'] ?? '') ?></td>
                <td><?= h($b['fiscal_year'] ?? '') ?></td>
                <td><?= h(fmt_money($b['budgeted_amount'])) ?></td>
                <td><?= h(fmt_money($b['committed_amount'])) ?></td>
                <td><?= h(fmt_money($b['actual_amount'])) ?></td>
                <td><?= h($b['funding_source_name'] ?? '—') ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_budget&project_id=<?= $pid ?>&edit_id=<?= (int)$b['budget_line_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_budget_delete&project_id=<?= $pid ?>&budget_line_id=<?= (int)$b['budget_line_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this budget line?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
