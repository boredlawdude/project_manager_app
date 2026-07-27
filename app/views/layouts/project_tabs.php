<?php
declare(strict_types=1);
/**
 * Shared workspace tab strip for a single project. Expects $project (array)
 * and $activeTab (string key) to be set by the including view.
 */
$tabs = [
    'details'  => ['label' => 'Project Details', 'page' => 'projects_show'],
    'tasks'    => ['label' => 'Tasks',            'page' => 'project_tasks'],
    'risks'    => ['label' => 'Risks',            'page' => 'project_risks'],
    'budget'   => ['label' => 'Budget',           'page' => 'project_budget'],
    'funding'  => ['label' => 'Funding',          'page' => 'project_funding'],
    'meetings' => ['label' => 'Meetings',         'page' => 'project_meetings'],
    'timeline' => ['label' => 'Timeline',         'page' => 'project_timeline'],
    'documents'=> ['label' => 'Documents',        'page' => 'project_documents'],
    'team'     => ['label' => 'Team',             'page' => 'project_team'],
];
$pid = (int)($project['project_id'] ?? 0);
?>
<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-3 mb-0"><?= h($project['project_name'] ?? '') ?></h1>
    <span class="badge text-bg-secondary text-uppercase"><?= h($project['status'] ?? '') ?></span>
</div>
<ul class="nav nav-tabs workspace-tabs mb-4">
    <?php foreach ($tabs as $key => $tab): ?>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === $key ? 'active' : '' ?>"
               href="/index.php?page=<?= h($tab['page']) ?>&project_id=<?= $pid ?>">
                <?= h($tab['label']) ?>
            </a>
        </li>
    <?php endforeach; ?>
    <li class="nav-item">
        <a class="nav-link" href="<?= h(contracts_app_url('index.php?page=contracts&project_id=' . $pid)) ?>" target="_blank" rel="noopener">
            Contracts &#8599;
        </a>
    </li>
</ul>
