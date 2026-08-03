<?php
declare(strict_types=1);
$pageTitle = 'Notes — ' . $project['project_name'];
require APP_ROOT . '/app/views/layouts/header.php';
$activeTab = 'notes';
require APP_ROOT . '/app/views/layouts/project_tabs.php';
$pid = (int)$project['project_id'];
?>

<div class="card shadow-sm mb-4">
    <div class="card-header"><?= $editNote ? 'Edit Note' : 'Add Note' ?></div>
    <div class="card-body">
        <form method="post" action="/index.php?page=<?= $editNote ? 'project_notes_update' : 'project_notes_store' ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <?php if ($editNote): ?><input type="hidden" name="note_id" value="<?= (int)$editNote['note_id'] ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small mb-0">Date</label>
                    <input type="date" name="note_date" class="form-control" value="<?= h($editNote['note_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-9">
                    <label class="form-label small mb-0">Note</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Note *" required><?= h($editNote['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editNote ? 'Save Changes' : 'Add Note' ?></button>
                <?php if ($editNote): ?>
                    <a href="/index.php?page=project_notes&project_id=<?= $pid ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm">
        <thead><tr><th style="width:110px;">Date</th><th style="width:180px;">User</th><th>Note</th><th></th></tr></thead>
        <tbody>
        <?php if (!$noteList): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No notes yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($noteList as $n): ?>
            <tr>
                <td><?= h(fmt_date($n['note_date'] ?? null)) ?></td>
                <td><?= h(trim((string)($n['author_name'] ?? '')) ?: '—') ?></td>
                <td style="white-space: pre-wrap;"><?= h($n['notes']) ?></td>
                <td class="text-end">
                    <a href="/index.php?page=project_notes&project_id=<?= $pid ?>&edit_id=<?= (int)$n['note_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="post" action="/index.php?page=project_notes_delete&project_id=<?= $pid ?>&note_id=<?= (int)$n['note_id'] ?>" class="d-inline" onsubmit="return confirm('Delete this note?');">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
