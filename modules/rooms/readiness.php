<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Room readiness';
$activePage = 'rooms';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("UPDATE rooms SET status='ready' WHERE id=? AND status='inspected'");
        $stmt->execute([$id]);
        if ($stmt->rowCount()) { set_flash('Room is ready for guests.'); }
        else { set_flash('A room must be inspected before it can be marked ready.', 'error'); }
    }
    redirect('/hotel_housekeeping_department/modules/rooms/readiness.php');
}
$rooms = $pdo->query("SELECT id, room_number, room_type, status FROM rooms WHERE status IN ('inspected','ready','out_of_order') ORDER BY room_number")->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">READY TO WELCOME</p><h2>Room readiness</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/rooms/index.php">← Room directory</a></div>
<?php if (!$rooms): ?><div class="empty-state"><span>⌂</span><h3>No inspected rooms yet</h3><p>Rooms appear here after inspection.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Room</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($rooms as $room): ?><tr><td><strong><?= e($room['room_number']) ?></strong></td><td><?= e($room['room_type']) ?></td><td><span class="badge badge-<?= e($room['status']) ?>"><?= e(ucwords(str_replace('_',' ', $room['status']))) ?></span></td><td><?php if ($room['status'] === 'inspected'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="room_id" value="<?= (int) $room['id'] ?>"><button class="button button-primary button-small">Mark ready</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
