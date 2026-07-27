<?php
declare(strict_types=1);
$pageTitle = 'Meetings — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'meetings';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editMeeting ? 'Edit Meeting' : 'Add Meeting' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editMeeting ? 'project_meetings_update' : 'project_meetings_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editMeeting): ?><input type="hidden" name="meeting_id" value="<?= (int)$editMeeting['meeting_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small mb-0">Date/Time *</label>
                    <input type="datetime-local" name="meeting_date" class="form-control" required
                           value="<?= h($editMeeting ? str_replace(' ', 'T', substr((string)$editMeeting['meeting_date'], 0, 16)) : '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="meeting_type" class="form-control" placeholder="Type (kickoff, status, etc.)" value="<?= h($editMeeting['meeting_type'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <input type="text" name="location" class="form-control" placeholder="Location" value="<?= h($editMeeting['location'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <textarea name="agenda" class="form-control" rows="2" placeholder="Agenda"><?= h($editMeeting['agenda'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <textarea name="minutes" class="form-control" rows="2" placeholder="Minutes"><?= h($editMeeting['minutes'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small mb-0">Attendees</label>
                    <select name="attendee_person_ids[]" class="form-select" multiple size="4">
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int)$person['person_id'] ?>" <?= in_array((int)$person['person_id'], $editAttendeeIds ?? [], true) ? 'selected' : '' ?>>
                                <?= h($person['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editMeeting ? 'Save Changes' : 'Add Meeting' ?></button>
                <?php if ($editMeeting): ?>
                    <a href="/index.php?page=project_meetings&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th>Date</th><th>Type</th><th>Location</th><th></th></tr></thead>
        <tbody>
        <?php if (!$meetingList): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No meetings logged yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($meetingList as $m): ?>
            <tr>
                <td><?= h(date('m/d/Y g:i A', strtotime((string)$m['meeting_date']))) ?></td>
                <td><?= h($m['meeting_type'] ?? '') ?></td>
                <td><?= h($m['location'] ?? '') ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_meetings&project_id=<?= $pid ?>&edit_id=<?= (int)$m['meeting_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_meetings_delete&project_id=<?= $pid ?>&meeting_id=<?= (int)$m['meeting_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this meeting?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
