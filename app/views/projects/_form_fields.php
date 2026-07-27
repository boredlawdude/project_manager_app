<?php
declare(strict_types=1);
/** Shared form fields for create/edit, expects $project, $departments, $people */
?>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Project Code *</label>
        <input type="text" name="project_code" class="form-control" required value="<?= h($project['project_code'] ?? '') ?>">
    </div>
    <div class="col-md-8">
        <label class="form-label">Project Name *</label>
        <input type="text" name="project_name" class="form-control" required value="<?= h($project['project_name'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= h($project['description'] ?? '') ?></textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <?php foreach (['proposed','active','on_hold','completed','cancelled'] as $s): ?>
                <option value="<?= h($s) ?>" <?= ($project['status'] ?? 'proposed') === $s ? 'selected' : '' ?>><?= h(ucwords(str_replace('_',' ',$s))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            <?php foreach (['low','medium','high','critical'] as $p): ?>
                <option value="<?= h($p) ?>" <?= ($project['priority'] ?? 'medium') === $p ? 'selected' : '' ?>><?= h(ucfirst($p)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= (int)$d['department_id'] ?>" <?= (string)($project['department_id'] ?? '') === (string)$d['department_id'] ? 'selected' : '' ?>>
                    <?= h($d['department_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Project Manager</label>
        <select name="project_manager_person_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>" <?= (string)($project['project_manager_person_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>>
                    <?= h($person['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Sponsor</label>
        <select name="sponsor_person_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>" <?= (string)($project['sponsor_person_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>>
                    <?= h($person['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Estimated Budget</label>
        <input type="number" step="0.01" name="estimated_budget" class="form-control" value="<?= h($project['estimated_budget'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="<?= h($project['start_date'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Target End Date</label>
        <input type="date" name="target_end_date" class="form-control" value="<?= h($project['target_end_date'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Actual End Date</label>
        <input type="date" name="actual_end_date" class="form-control" value="<?= h($project['actual_end_date'] ?? '') ?>">
    </div>
</div>
