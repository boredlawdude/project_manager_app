<?php
declare(strict_types=1);
$pageTitle = 'Gantt Chart — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'gantt';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@1.2.2/dist/frappe-gantt.min.css">
<style>
    #gantt-container { background: #fff; overflow-x: auto; }
    .bar-wrapper.gantt-task-blocked .bar { fill: #dc3545; }
    .bar-wrapper.gantt-task-blocked .bar-progress { fill: #a52834; }
    .bar-wrapper.gantt-task-done .bar { fill: #198754; }
    .bar-wrapper.gantt-task-done .bar-progress { fill: #145c3a; }
    .gantt-legend .badge { font-weight: 400; }
</style>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="gantt-legend d-flex gap-2 align-items-center">
        <span class="badge" style="background:#a3a3a3;">&nbsp;</span> <span class="small text-muted me-2">Not started / In progress</span>
        <span class="badge" style="background:#dc3545;">&nbsp;</span> <span class="small text-muted me-2">Blocked</span>
        <span class="badge" style="background:#198754;">&nbsp;</span> <span class="small text-muted">Completed</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="gantt-save-status" class="small text-muted"></span>
        <a href="/index.php?page=project_tasks&project_id=<?= $pid ?>" class="btn btn-sm btn-outline-secondary">Manage Tasks</a>
    </div>
</div>

<?php if ($ganttTasks): ?>
    <div class="alert alert-info py-2 small mb-2">
        Drag a bar to move it, or drag its edges to resize it — start/due dates are saved automatically.
    </div>
<?php endif; ?>

<?php if ($skippedCount > 0): ?>
    <div class="alert alert-warning py-2 small">
        <?= (int)$skippedCount ?> task(s) are not shown because they have no start or due date set.
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body p-0">
        <?php if (!$ganttTasks): ?>
            <div class="text-center text-muted py-5">No tasks with dates to display yet. Add start/due dates on the Tasks tab.</div>
        <?php else: ?>
            <div id="gantt-container"><svg id="gantt"></svg></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($ganttTasks): ?>
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@1.2.2/dist/frappe-gantt.umd.min.js"></script>
<script>
    var ganttTasks = <?= json_encode($ganttTasks, JSON_UNESCAPED_SLASHES) ?>;
    var ganttProjectId = <?= $pid ?>;

    function formatLocalDate(d) {
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var statusEl = document.getElementById('gantt-save-status');

        new Gantt('#gantt', ganttTasks, {
            view_mode: 'Week',
            view_mode_select: true,
            popup_on: 'hover',
            on_date_change: function (task, start, end) {
                var body = new URLSearchParams();
                body.append('project_id', ganttProjectId);
                body.append('task_id', task.id);
                body.append('start_date', formatLocalDate(start));
                body.append('due_date', formatLocalDate(end));

                statusEl.textContent = 'Saving…';
                fetch('/index.php?page=project_gantt_update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        statusEl.textContent = data.ok ? 'Saved.' : 'Failed to save: ' + (data.error || 'unknown error');
                    })
                    .catch(function () {
                        statusEl.textContent = 'Failed to save (network error).';
                    });
            }
        });
    });
</script>
<?php endif; ?>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
