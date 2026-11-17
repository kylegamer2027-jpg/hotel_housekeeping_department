<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$taskId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
if (!$taskId) { http_response_code(400); exit('A valid task is required.'); }
$stmt = $pdo->prepare('SELECT id, title FROM tasks WHERE id = ?');
$stmt->execute([$taskId]);
$task = $stmt->fetch();
if (!$task) { http_response_code(404); exit('Task not found.'); }
$pageTitle = 'Task checklist';
$activePage = 'tasks';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['add_item'])) {
        $item = trim((string) ($_POST['item_text'] ?? ''));
        if ($item !== '') {
            $insert = $pdo->prepare('INSERT INTO task_checklist (task_id, item_text) VALUES (?, ?)');
            $insert->execute([$taskId, $item]);
        }
    } elseif (isset($_POST['toggle_id'])) {
        $toggle = $pdo->prepare('UPDATE task_checklist SET is_done = 1 - is_done WHERE id = ? AND task_id = ?');
        $toggle->execute([(int) $_POST['toggle_id'], $taskId]);
    } elseif (isset($_POST['delete_id'])) {
        $delete = $pdo->prepare('DELETE FROM task_checklist WHERE id = ? AND task_id = ?');
        $delete->execute([(int) $_POST['delete_id'], $taskId]);
    }
    redirect('/hotel_housekeeping_department/modules/tasks/checklist.php?id=' . $taskId);
}
$itemsQuery = $pdo->prepare('SELECT * FROM task_checklist WHERE task_id = ? ORDER BY id');
$itemsQuery->execute([$taskId]);
$items = $itemsQuery->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">ONE DETAIL AT A TIME</p><h2><?= e($task['title']) ?></h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/index.php">← Back to tasks</a></div>
<form method="post" class="toolbar"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="task_id" value="<?= $taskId ?>"><input name="item_text" required maxlength="255" placeholder="Add a checklist item"><button name="add_item" class="button button-primary">Add item</button></form>
<?php if (!$items): ?><div class="empty-state"><span>✿</span><h3>A clean slate</h3><p>Add checklist items to keep the task on track.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Done</th><th>Checklist item</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="task_id" value="<?= $taskId ?>"><input type="hidden" name="toggle_id" value="<?= (int) $item['id'] ?>"><input type="checkbox" <?= $item['is_done'] ? 'checked' : '' ?> onchange="this.form.submit()" aria-label="Mark item done"></form></td><td><?= $item['is_done'] ? '<s>' . e($item['item_text']) . '</s>' : e($item['item_text']) ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="task_id" value="<?= $taskId ?>"><input type="hidden" name="delete_id" value="<?= (int) $item['id'] ?>"><button class="button button-danger button-small" data-confirm="Remove this checklist item?">Remove</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
