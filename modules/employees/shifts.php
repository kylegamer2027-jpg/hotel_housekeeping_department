<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role(['admin','supervisor']);
$pageTitle = 'Team shifts';
$activePage = 'employees';
$shiftPresets = [
    'morning' => ['label' => 'Morning · 7:00 AM–3:00 PM', 'start' => '07:00', 'end' => '15:00'],
    'day' => ['label' => 'Day · 9:00 AM–5:00 PM', 'start' => '09:00', 'end' => '17:00'],
    'mid' => ['label' => 'Mid shift · 11:00 AM–7:00 PM', 'start' => '11:00', 'end' => '19:00'],
    'evening' => ['label' => 'Evening · 3:00 PM–11:00 PM', 'start' => '15:00', 'end' => '23:00'],
];
$employees = $pdo->query('SELECT id, name FROM users ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $date = (string) ($_POST['shift_date'] ?? '');
    $start = (string) ($_POST['start_time'] ?? '');
    $end = (string) ($_POST['end_time'] ?? '');
    if (!$userId || !$date || !$start || !$end || $end <= $start) {
        set_flash('Choose a team member, date, and valid start/end times on the same day.', 'error');
    } else {
        $stmt = $pdo->prepare('INSERT INTO shifts (user_id, shift_date, start_time, end_time, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $date, $start, $end, trim((string) ($_POST['notes'] ?? '')) ?: null]);
        set_flash('Team shift added.');
    }
    redirect('/hotel_housekeeping_department/modules/employees/shifts.php');
}
$shifts = $pdo->query('SELECT s.*, u.name FROM shifts s JOIN users u ON u.id=s.user_id WHERE s.shift_date >= CURDATE() ORDER BY s.shift_date, s.start_time LIMIT 100')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">MAKE THE DAY FLOW</p><h2>Schedule a team shift</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/employees/index.php">← Team</a></div><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Suggested shift<select data-shift-preset><option value="">Choose hours or enter custom times</option><?php foreach ($shiftPresets as $key => $preset): ?><option value="<?= e($key) ?>" data-start="<?= e($preset['start']) ?>" data-end="<?= e($preset['end']) ?>"><?= e($preset['label']) ?></option><?php endforeach; ?></select></label><label>Team member<select name="user_id" required><?php foreach ($employees as $employee): ?><option value="<?= (int) $employee['id'] ?>"><?= e($employee['name']) ?></option><?php endforeach; ?></select></label><label>Date<input type="date" name="shift_date" min="<?= e(date('Y-m-d')) ?>" required></label><label>Starts<input type="time" name="start_time" required></label><label>Ends<input type="time" name="end_time" required></label><label>Notes<input name="notes" maxlength="255" placeholder="Optional notes"></label><div class="form-actions"><button class="button button-primary">Add shift</button></div></form></section>
<section class="panel"><div class="panel-heading"><h2>Upcoming shifts</h2></div><?php if (!$shifts): ?><div class="empty-state"><span>♙</span><h3>No shifts scheduled</h3><p>Plan the upcoming team schedule above.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Team member</th><th>Date</th><th>Hours</th><th>Notes</th></tr></thead><tbody><?php foreach ($shifts as $shift): ?><tr><td><strong><?= e($shift['name']) ?></strong></td><td><?= e(date('D, M j', strtotime($shift['shift_date']))) ?></td><td><?= e(date('g:i A', strtotime($shift['start_time']))) ?> – <?= e(date('g:i A', strtotime($shift['end_time']))) ?></td><td><?= e($shift['notes'] ?? '—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
