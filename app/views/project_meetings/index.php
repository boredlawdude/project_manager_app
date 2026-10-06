<?php
declare(strict_types=1);
$pageTitle = 'Meetings — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'meetings';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<?php if (!empty($emailSuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= h($emailSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($emailError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($emailError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
                    <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto; column-width: 180px; column-gap: 1rem;">
                        <?php foreach ($people as $person): ?>
                            <div class="form-check text-nowrap mb-1" style="break-inside: avoid;">
                                <input class="form-check-input" type="checkbox" name="attendee_person_ids[]"
                                       id="attendee_<?= (int)$person['person_id'] ?>" value="<?= (int)$person['person_id'] ?>"
                                       <?= in_array((int)$person['person_id'], $editAttendeeIds ?? [], true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="attendee_<?= (int)$person['person_id'] ?>">
                                    <?= h($person['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
        <thead><tr><th>Date</th><th>Type</th><th>Location</th><th>Minutes</th><th></th></tr></thead>
        <tbody>
        <?php if (!$meetingList): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No meetings logged yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($meetingList as $m): ?>
            <?php $mid = (int)$m['meeting_id']; $hasMinutes = trim((string)($m['minutes'] ?? '')) !== ''; ?>
            <tr>
                <td><?= h(date('m/d/Y g:i A', strtotime((string)$m['meeting_date']))) ?></td>
                <td><?= h($m['meeting_type'] ?? '') ?></td>
                <td><?= h($m['location'] ?? '') ?></td>
                <td>
                    <?php if ($hasMinutes): ?>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#minutesModal<?= $mid ?>">View Minutes</a>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <a href="/index.php?page=project_meetings&project_id=<?= $pid ?>&edit_id=<?= $mid ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_meetings_delete&project_id=<?= $pid ?>&meeting_id=<?= $mid ?>" class="d-inline" onsubmit="return confirm('Delete this meeting?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php foreach ($meetingList as $m): ?>
    <?php
    $mid = (int)$m['meeting_id'];
    $hasMinutes = trim((string)($m['minutes'] ?? '')) !== '';
    if (!$hasMinutes) { continue; }
    $participants = $meetingAttendees[$mid] ?? [];
    ?>
    <div class="modal fade" id="minutesModal<?= $mid ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Meeting Minutes — <?= h(date('m/d/Y g:i A', strtotime((string)$m['meeting_date']))) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="white-space: pre-wrap;"><?= h($m['minutes']) ?></p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="/index.php?page=project_meetings_email">
                        <input type="hidden" name="project_id" value="<?= $pid ?>">
                        <input type="hidden" name="meeting_id" value="<?= $mid ?>">
                        <button type="submit" class="btn btn-primary btn-sm" <?= $participants ? '' : 'disabled' ?>
                                title="<?= $participants ? 'Email minutes to all participants' : 'No participants with an email address' ?>">
                            Email to Participants
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>

