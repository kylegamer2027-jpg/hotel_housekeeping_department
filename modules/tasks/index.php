<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Housekeeping tasks';
$activePage = 'tasks';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');
    if ($id && in_array($status, ['pending', 'in_progress', 'completed'], true)) {
        $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        set_flash('Task status updated.');
    }
    redirect('/hotel_housekeeping_department/modules/tasks/index.php');
}
$tasks = $pdo->query('SELECT t.*, r.room_number, u.name AS assigned_name FROM tasks t LEFT JOIN rooms r ON r.id=t.room_id LEFT JOIN users u ON u.id=t.assigned_to ORDER BY (t.status = "completed"), t.due_date IS NULL, t.due_date, t.id DESC')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel">
    <div class="toolbar"><h2>Today's care list</h2><div class="toolbar-actions"><a class="button button-secondary" href="/hotel_housekeeping_department/modules/tasks/assign.php">Assign tasks</a><a class="button button-primary" href="/hotel_housekeeping_department/modules/tasks/create.php">＋ Create task</a></div></div>
    <?php if (!$tasks): ?><div class="empty-state"><span>✿</span><h3>No tasks just yet</h3><p>Create a task to get the day started.</p></div><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Task</th><th>Room</th><th>Team member</th><th>Priority</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($tasks as $task): ?><tr><td><strong><?= e($task['title']) ?></strong><small class="cell-sub"><?= e($task['description'] ?: 'No additional notes') ?></small></td><td><?= e($task['room_number'] ?? '—') ?></td><td><?= e($task['assigned_name'] ?? 'Unassigned') ?></td><td><span class="badge badge-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span></td><td><?= $task['due_date'] ? e(date('M j, Y', strtotime($task['due_date']))) : '—' ?></td><td><span class="badge badge-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></td><td><div class="toolbar-actions"><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/checklist.php?id=<?= (int) $task['id'] ?>">Checklist</a><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $task['id'] ?>"><select name="status" aria-label="Task status"><?php foreach (['pending','in_progress','completed'] as $s): ?><option value="<?= $s ?>" <?= $task['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></div></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
