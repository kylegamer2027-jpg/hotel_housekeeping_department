<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Assign tasks';
$activePage = 'tasks';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $taskId = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $userId = ($_POST['user_id'] ?? '') !== '' ? filter_var($_POST['user_id'], FILTER_VALIDATE_INT) : null;
    if ($taskId) {
        $stmt = $pdo->prepare('UPDATE tasks SET assigned_to = ? WHERE id = ?');
        $stmt->execute([$userId ?: null, $taskId]);
        set_flash('Task assignment saved.');
    }
    redirect('/hotel_housekeeping_department/modules/tasks/assign.php');
}
$tasks = $pdo->query("SELECT id, title, assigned_to FROM tasks WHERE status != 'completed' ORDER BY id DESC")->fetchAll();
$users = $pdo->query('SELECT id, name FROM users ORDER BY name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">TEAM COORDINATION</p><h2>Put the right hands on each task</h2></div></div>
<?php if (!$tasks): ?><div class="empty-state"><span>✿</span><h3>No open tasks to assign</h3><p>New tasks will appear here.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Task</th><th>Assignment</th><th></th></tr></thead><tbody><?php foreach ($tasks as $task): ?><tr><td><strong><?= e($task['title']) ?></strong></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="task_id" value="<?= (int) $task['id'] ?>"><select name="user_id"><option value="">Unassigned</option><?php foreach ($users as $user): ?><option value="<?= (int) $user['id'] ?>" <?= (int) $task['assigned_to'] === (int) $user['id'] ? 'selected' : '' ?>><?= e($user['name']) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></td><td></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
