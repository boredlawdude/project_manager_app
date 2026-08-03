<?php
declare(strict_types=1);
require APP_ROOT . '/app/views/layouts/header.php';

$displayName = $person['name'] ?? 'there';
$title = trim((string)($person['title'] ?? ''));
$today = new DateTimeImmutable('today');
?>

<div class="mb-4">
  <h1 class="h3 mb-1">Hello, <?= h($displayName) ?></h1>
  <p class="text-muted mb-0">
    You are the <?= $title !== '' ? '<strong>' . h($title) . '</strong>' : '<em>(no title set)</em>' ?><?= $orgName !== '' ? ' for ' . h($orgName) : '' ?>.
  </p>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold">
    You have the following Tasks for the following Projects
  </div>
  <div class="card-body p-0">
    <?php if (empty($myTasks)): ?>
      <div class="p-4 text-muted">No open tasks assigned to you. Enjoy the quiet!</div>
    <?php else: ?>
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Task</th>
            <th>Project</th>
            <th>Due Date</th>
            <th>Days to Due Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($myTasks as $task): ?>
            <?php
              $daysLabel = '—';
              $daysClass = 'text-muted';
              if (!empty($task['due_date'])) {
                  $due = new DateTimeImmutable($task['due_date']);
                  $diff = $today->diff($due);
                  $days = (int)$diff->format('%r%a');
                  if ($days < 0) {
                      $daysLabel = abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue';
                      $daysClass = 'text-danger fw-semibold';
                  } elseif ($days === 0) {
                      $daysLabel = 'Due today';
                      $daysClass = 'text-warning fw-semibold';
                  } else {
                      $daysLabel = $days . ' day' . ($days === 1 ? '' : 's');
                      $daysClass = $days <= 7 ? 'text-warning fw-semibold' : 'text-muted';
                  }
              }
            ?>
            <tr>
              <td><?= h($task['task_name']) ?></td>
              <td><?= h($task['project_name']) ?></td>
              <td><?= h(fmt_date($task['due_date'])) ?></td>
              <td class="<?= $daysClass ?>"><?= h($daysLabel) ?></td>
              <td><span class="badge bg-secondary"><?= h(str_replace('_', ' ', $task['status'])) ?></span></td>
              <td>
                <a href="/index.php?page=project_tasks&project_id=<?= (int)$task['project_id'] ?>" class="btn btn-sm btn-outline-primary">View Project</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
