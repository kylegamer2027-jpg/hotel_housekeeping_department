<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Room status';
$activePage = 'rooms';
$transitions = [
    'dirty' => ['in_progress', 'out_of_order'],
    'in_progress' => ['dirty', 'out_of_order'],
    'inspected' => ['dirty', 'out_of_order'],
    'ready' => ['dirty', 'out_of_order'],
    'out_of_order' => ['dirty'],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');
    if ($id && in_array($status, ['dirty','in_progress','inspected','ready','out_of_order'], true)) {
        $currentQuery = $pdo->prepare('SELECT status FROM rooms WHERE id=?');
        $currentQuery->execute([$id]);
        $currentStatus = $currentQuery->fetchColumn();
        if ($currentStatus !== false && ($status === $currentStatus || in_array($status, $transitions[$currentStatus] ?? [], true))) {
            $stmt = $pdo->prepare('UPDATE rooms SET status=? WHERE id=?');
            $stmt->execute([$status, $id]);
            set_flash('Room status updated.');
        } else {
            set_flash('Rooms must pass inspection before being marked ready.', 'error');
        }
    }
    redirect('/hotel_housekeeping_department/modules/rooms/status.php');
}
$rooms = $pdo->query('SELECT id, room_number, status FROM rooms ORDER BY room_number')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A ROOM FOR EVERY MOMENT</p><h2>Update room status</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/rooms/index.php">← Room directory</a></div>
<?php if (!$rooms): ?><div class="empty-state"><span>⌂</span><h3>No rooms yet</h3><p>Add rooms to start managing their status.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Room</th><th>Current status</th><th>Change status</th></tr></thead><tbody><?php foreach ($rooms as $room): ?><tr><td><strong><?= e($room['room_number']) ?></strong></td><td><span class="badge badge-<?= e($room['status']) ?>"><?= e(ucwords(str_replace('_',' ', $room['status']))) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="room_id" value="<?= (int) $room['id'] ?>"><select name="status"><?php foreach (array_merge([$room['status']], $transitions[$room['status']] ?? []) as $status): ?><option value="<?= e($status) ?>"><?= e(ucwords(str_replace('_',' ', $status))) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
