<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Room inspection';
$activePage = 'rooms';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("UPDATE rooms SET status='inspected' WHERE id=? AND status IN ('dirty','in_progress')");
        $stmt->execute([$id]);
        if ($stmt->rowCount()) { set_flash('Room inspection recorded.'); }
        else { set_flash('Only rooms being cleaned can be marked inspected.', 'error'); }
    }
    redirect('/hotel_housekeeping_department/modules/rooms/inspection.php');
}
$rooms = $pdo->query("SELECT id, room_number, room_type, status FROM rooms WHERE status IN ('dirty','in_progress','inspected') ORDER BY room_number")->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">THE FINAL CHECK</p><h2>Inspect the details</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/rooms/readiness.php">Room readiness →</a></div>
<?php if (!$rooms): ?><div class="empty-state"><span>✿</span><h3>Nothing waiting for inspection</h3><p>Rooms being cleaned will appear here.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Room</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($rooms as $room): ?><tr><td><strong><?= e($room['room_number']) ?></strong></td><td><?= e($room['room_type']) ?></td><td><span class="badge badge-<?= e($room['status']) ?>"><?= e(ucwords(str_replace('_',' ', $room['status']))) ?></span></td><td><?php if ($room['status'] !== 'inspected'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="room_id" value="<?= (int) $room['id'] ?>"><button class="button button-primary button-small">Mark inspected</button></form><?php else: ?><span class="badge badge-inspected">Inspected</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
