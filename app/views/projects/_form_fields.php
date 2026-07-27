<?php
declare(strict_types=1);
/** Shared form fields for create/edit, expects $project, $departments, $people, $statuses, $priorities, $projectTypes, $defaultTasksByType */
$isNewProject = empty($project['project_id']);
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
    <div class="col-md-4">
        <label class="form-label">Project Type</label>
        <select name="project_type_id" id="projectTypeSelect" class="form-select">
            <option value="">—</option>
            <?php foreach ($projectTypes as $t): ?>
                <option value="<?= (int)$t['project_type_id'] ?>" <?= (string)($project['project_type_id'] ?? '') === (string)$t['project_type_id'] ? 'selected' : '' ?>>
                    <?= h($t['project_type_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <?php foreach ($statuses as $s): ?>
                <option value="<?= h($s['status_name']) ?>" <?= ($project['status'] ?? 'proposed') === $s['status_name'] ? 'selected' : '' ?>><?= h(ucwords(str_replace('_',' ',$s['status_name']))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            <?php foreach ($priorities as $p): ?>
                <option value="<?= h($p['priority_name']) ?>" <?= ($project['priority'] ?? 'medium') === $p['priority_name'] ? 'selected' : '' ?>><?= h(ucfirst($p['priority_name'])) ?></option>
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

    <?php if ($isNewProject): ?>
    <div class="col-12" id="defaultTasksSection" style="display:none">
        <label class="form-label fw-bold">Suggested Tasks for this Project Type</label>
        <div class="form-text text-muted mb-2">Check any of the standard tasks below to automatically add them to this project.</div>
        <div id="defaultTasksList" class="border rounded p-3 bg-white"></div>
    </div>
    <script>
        (function () {
            var tasksByType = <?= $defaultTasksByType ?? '{}' ?>;
            var typeSelect = document.getElementById('projectTypeSelect');
            var section = document.getElementById('defaultTasksSection');
            var list = document.getElementById('defaultTasksList');

            function render() {
                var tasks = tasksByType[typeSelect.value] || [];
                list.innerHTML = '';
                if (!tasks.length) {
                    section.style.display = 'none';
                    return;
                }
                tasks.forEach(function (t) {
                    var wrap = document.createElement('div');
                    wrap.className = 'form-check';
                    var label = t.name + (t.description ? ' — ' + t.description : '');
                    wrap.innerHTML = '<input class="form-check-input" type="checkbox" name="default_task_ids[]" value="' + t.id + '" id="dt' + t.id + '">' +
                        '<label class="form-check-label" for="dt' + t.id + '"></label>';
                    wrap.querySelector('label').textContent = label;
                    list.appendChild(wrap);
                });
                section.style.display = '';
            }

            typeSelect.addEventListener('change', render);
            render();
        })();
    </script>
    <?php endif; ?>
</div>

