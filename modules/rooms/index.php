<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Rooms';
$activePage = 'rooms';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $number = trim((string) ($_POST['room_number'] ?? ''));
    if ($number === '') {
        $error = 'Please provide a room number.';
    } else {
        try {
            $insert = $pdo->prepare('INSERT INTO rooms (room_number, room_type, floor_number) VALUES (?, ?, ?)');
            $insert->execute([$number, trim((string) ($_POST['room_type'] ?? 'Standard')) ?: 'Standard', ($_POST['floor_number'] ?? '') !== '' ? (int) $_POST['floor_number'] : null]);
            set_flash('Room added to your room list.');
            redirect('/hotel_housekeeping_department/modules/rooms/index.php');
        } catch (PDOException $exception) {
            $error = 'That room number already exists.';
        }
    }
}
$rooms = $pdo->query('SELECT r.*, (SELECT COUNT(*) FROM tasks t WHERE t.room_id=r.id AND t.status!="completed") AS open_tasks FROM rooms r ORDER BY room_number')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="toolbar"><h2>Room directory</h2><div class="toolbar-actions"><a class="button button-secondary" href="/hotel_housekeeping_department/modules/rooms/status.php">Update status</a><a class="button button-secondary" href="/hotel_housekeeping_department/modules/rooms/inspection.php">Inspections</a><a class="button button-secondary" href="/hotel_housekeeping_department/modules/rooms/readiness.php">Readiness</a></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="toolbar"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input name="room_number" required maxlength="20" placeholder="Room number"><input name="room_type" maxlength="80" placeholder="Room type (e.g. Suite)"><input type="number" name="floor_number" placeholder="Floor" style="max-width:100px"><button class="button button-primary">＋ Add room</button></form>
<?php if (!$rooms): ?><div class="empty-state"><span>⌂</span><h3>Your room list starts here</h3><p>Add your rooms to manage their daily status.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Room</th><th>Type</th><th>Floor</th><th>Open tasks</th><th>Status</th></tr></thead><tbody><?php foreach ($rooms as $room): ?><tr><td><strong><?= e($room['room_number']) ?></strong></td><td><?= e($room['room_type']) ?></td><td><?= e($room['floor_number'] ?? '—') ?></td><td><?= (int) $room['open_tasks'] ?></td><td><span class="badge badge-<?= e($room['status']) ?>"><?= e(ucwords(str_replace('_',' ', $room['status']))) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
