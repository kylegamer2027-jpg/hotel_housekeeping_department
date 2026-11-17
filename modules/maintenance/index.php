<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Maintenance';
$activePage = 'maintenance';
$rooms = $pdo->query('SELECT id, room_number FROM rooms ORDER BY room_number')->fetchAll();
$issueSuggestions = [
    ['title' => 'Leaking tap or pipe', 'description' => 'Describe where the leak is and whether water is still running.', 'priority' => 'high'],
    ['title' => 'Air conditioning not working', 'description' => 'Note whether the unit has power and what issue the guest or team observed.', 'priority' => 'high'],
    ['title' => 'Light or electrical issue', 'description' => 'Identify the fixture and whether it is flickering, damaged, or not working.', 'priority' => 'normal'],
    ['title' => 'Door, lock, or key issue', 'description' => 'Describe the door or lock problem and any access or safety concern.', 'priority' => 'urgent'],
    ['title' => 'Appliance not working', 'description' => 'Name the appliance and describe the problem or any error shown.', 'priority' => 'normal'],
    ['title' => 'Furniture or fixture repair', 'description' => 'Describe the damaged or loose furniture or fixture.', 'priority' => 'normal'],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['status_id'])) {
        $status = (string) ($_POST['status'] ?? '');
        if (in_array($status, ['open','in_progress','completed'], true)) {
            $stmt = $pdo->prepare('UPDATE maintenance_requests SET status=? WHERE id=?');
            $stmt->execute([$status, (int) $_POST['status_id']]);
            set_flash('Maintenance request updated.');
        }
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            set_flash('Please describe the maintenance issue.', 'error');
        } else {
            $stmt = $pdo->prepare('INSERT INTO maintenance_requests (title, description, room_id, priority, reported_by) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, trim((string) ($_POST['description'] ?? '')) ?: null, ($_POST['room_id'] ?? '') !== '' ? (int) $_POST['room_id'] : null, in_array($_POST['priority'] ?? '', ['low','normal','high','urgent'], true) ? $_POST['priority'] : 'normal', current_user()['id']]);
            set_flash('Maintenance request created.');
        }
    }
    redirect('/hotel_housekeeping_department/modules/maintenance/index.php');
}
$requests = $pdo->query('SELECT m.*, r.room_number, u.name AS reporter FROM maintenance_requests m LEFT JOIN rooms r ON r.id=m.room_id LEFT JOIN users u ON u.id=m.reported_by ORDER BY (m.status="completed"), FIELD(m.priority,"urgent","high","normal","low"), m.created_at DESC')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">KEEP EVERYTHING IN GOOD REPAIR</p><h2>Report an issue</h2></div></div><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Suggested issue<select data-suggestion-prefill data-title-target="maintenance-title" data-description-target="maintenance-description" data-priority-target="maintenance-priority"><option value="">Choose an issue suggestion or enter your own</option><?php foreach ($issueSuggestions as $suggestion): ?><option value="<?= e($suggestion['title']) ?>" data-title="<?= e($suggestion['title']) ?>" data-description="<?= e($suggestion['description']) ?>" data-priority="<?= e($suggestion['priority']) ?>"><?= e($suggestion['title']) ?></option><?php endforeach; ?></select></label><label>Issue title<input id="maintenance-title" name="title" maxlength="180" required placeholder="e.g. Bathroom tap is leaking"></label><label>Room<select name="room_id"><option value="">Common area / not listed</option><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>"><?= e($room['room_number']) ?></option><?php endforeach; ?></select></label><label>Priority<select id="maintenance-priority" name="priority"><?php foreach (['low','normal','high','urgent'] as $p): ?><option value="<?= $p ?>"><?= e(ucfirst($p)) ?></option><?php endforeach; ?></select></label><label>Details<textarea id="maintenance-description" name="description" placeholder="Share a little more detail"></textarea></label><div class="form-actions"><button class="button button-primary">Create request</button></div></form></section>
<section class="panel"><div class="panel-heading"><h2>Maintenance requests</h2></div><?php if (!$requests): ?><div class="empty-state"><span>⚒</span><h3>All in good order</h3><p>New maintenance requests will show up here.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Issue</th><th>Room</th><th>Priority</th><th>Reported</th><th>Status</th><th>Update</th></tr></thead><tbody><?php foreach ($requests as $request): ?><tr><td><strong><?= e($request['title']) ?></strong><small class="cell-sub"><?= e($request['description'] ?? '') ?></small></td><td><?= e($request['room_number'] ?? 'Common') ?></td><td><span class="badge badge-<?= e($request['priority']) ?>"><?= e(ucfirst($request['priority'])) ?></span></td><td><?= e($request['reporter'] ?? '—') ?></td><td><span class="badge badge-<?= e($request['status']) ?>"><?= e(ucwords(str_replace('_',' ', $request['status']))) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="status_id" value="<?= (int) $request['id'] ?>"><select name="status"><?php foreach (['open','in_progress','completed'] as $s): ?><option value="<?= $s ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_',' ', $s))) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
