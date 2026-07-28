<?php
declare(strict_types=1);
$pageTitle = 'Contracts — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'contracts';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="<?= h(contracts_app_url('index.php?page=contracts&project_id=' . $pid)) ?>" target="_blank" rel="noopener">
        Open in Contracts App &#8599;
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">Linked Contracts</div>
    <div class="card-body">
        <?php if (empty($linkedContracts)): ?>
            <p class="text-muted mb-0">No contracts are linked to this project yet.</p>
        <?php else: ?>
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Contract #</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Value</th>
                        <th>Start</th>
                        <th>End</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($linkedContracts as $c): ?>
                        <tr>
                            <td><?= h($c['contract_number'] ?? '') ?></td>
                            <td>
                                <a href="<?= h(contracts_app_url('index.php?page=contracts_show&contract_id=' . (int)$c['contract_id'])) ?>" target="_blank" rel="noopener">
                                    <?= h($c['name'] ?? '') ?>
                                </a>
                            </td>
                            <td><?= h($c['contract_status_name'] ?? '') ?></td>
                            <td><?= h(fmt_money($c['total_contract_value'] ?? null)) ?></td>
                            <td><?= h(fmt_date($c['start_date'] ?? null)) ?></td>
                            <td><?= h(fmt_date($c['end_date'] ?? null)) ?></td>
                            <td class="text-end">
                                <form method="post" action="/index.php?page=project_contracts_unlink" onsubmit="return confirm('Unlink this contract from the project?');">
                                    <input type="hidden" name="project_id" value="<?= $pid ?>">
                                    <input type="hidden" name="contract_id" value="<?= (int)$c['contract_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Unlink</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">Link Existing Contract</div>
    <div class="card-body">
        <?php if (empty($unlinkedContracts)): ?>
            <p class="text-muted mb-0">No unlinked contracts are available in the Contracts App.</p>
        <?php else: ?>
            <form method="post" action="/index.php?page=project_contracts_link" class="row g-2 align-items-center">
                <input type="hidden" name="project_id" value="<?= $pid ?>">
                <div class="col-md-8">
                    <input type="text" id="contractLinkFilter" class="form-control form-control-sm mb-2"
                           placeholder="Type to search <?= count($unlinkedContracts) ?> unlinked contracts…">
                    <select name="contract_id" id="contractLinkSelect" class="form-select" size="8" required>
                        <?php foreach ($unlinkedContracts as $c): ?>
                            <option value="<?= (int)$c['contract_id'] ?>"
                                    data-search="<?= h(strtolower(($c['contract_number'] ?? '') . ' ' . ($c['name'] ?? ''))) ?>">
                                <?= h($c['contract_number'] ?: ('#' . $c['contract_id'])) ?> — <?= h($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm">Link to This Project</button>
                </div>
            </form>
            <script>
                document.getElementById('contractLinkFilter').addEventListener('input', function () {
                    var q = this.value.trim().toLowerCase();
                    document.querySelectorAll('#contractLinkSelect option').forEach(function (opt) {
                        opt.hidden = q !== '' && opt.dataset.search.indexOf(q) === -1;
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
