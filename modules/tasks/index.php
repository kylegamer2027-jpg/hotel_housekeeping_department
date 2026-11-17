<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Housekeeping tasks';
$activePage = 'tasks';
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$priorityFilter = (string) ($_GET['priority'] ?? '');
$validStatuses = ['pending', 'in_progress', 'completed'];
$validPriorities = ['low', 'normal', 'high', 'urgent'];
if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}
if (!in_array($priorityFilter, $validPriorities, true)) {
    $priorityFilter = '';
}
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
$conditions = [];
$params = [];
if ($search !== '') {
    $conditions[] = '(t.title LIKE ? OR t.description LIKE ? OR r.room_number LIKE ? OR u.name LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term, $term);
}
if ($statusFilter !== '') {
    $conditions[] = 't.status = ?';
    $params[] = $statusFilter;
}
if ($priorityFilter !== '') {
    $conditions[] = 't.priority = ?';
    $params[] = $priorityFilter;
}
$sql = 'SELECT t.*, r.room_number, u.name AS assigned_name, (SELECT COUNT(*) FROM task_checklist c WHERE c.task_id=t.id) AS checklist_count, (SELECT COUNT(*) FROM task_checklist c WHERE c.task_id=t.id AND c.is_done=1) AS checklist_done FROM tasks t LEFT JOIN rooms r ON r.id=t.room_id LEFT JOIN users u ON u.id=t.assigned_to';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY (t.status = "completed"), t.due_date IS NULL, t.due_date, t.id DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$tasks = $statement->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel">
    <div class="toolbar"><div><p class="eyebrow">KEEP THE DAY MOVING</p><h2>Today's care list</h2></div><div class="toolbar-actions"><a class="button button-secondary" href="/hotel_housekeeping_department/modules/tasks/assign.php">Assign tasks</a><a class="button button-primary" href="/hotel_housekeeping_department/modules/tasks/create.php">＋ Create task</a></div></div>
    <form method="get" class="filter-bar">
        <label class="filter-search">Search tasks<input type="search" name="search" value="<?= e($search) ?>" placeholder="Task, room, or team member"></label>
        <label>Status<select name="status"><option value="">All statuses</option><?php foreach ($validStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></label>
        <label>Priority<select name="priority"><option value="">All priorities</option><?php foreach ($validPriorities as $priority): ?><option value="<?= e($priority) ?>" <?= $priorityFilter === $priority ? 'selected' : '' ?>><?= e(ucfirst($priority)) ?></option><?php endforeach; ?></select></label>
        <div class="filter-actions"><button class="button button-secondary">Filter</button><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/index.php">Clear</a></div>
    </form>
    <?php if (!$tasks): ?><div class="empty-state"><span>✿</span><h3><?= $search !== '' || $statusFilter !== '' || $priorityFilter !== '' ? 'No matching tasks' : 'No tasks just yet' ?></h3><p><?= $search !== '' || $statusFilter !== '' || $priorityFilter !== '' ? 'Try adjusting your search or filters.' : 'Create a task to get the day started.' ?></p></div><?php else: ?>
    <div class="results-note">Showing <strong><?= count($tasks) ?></strong> task<?= count($tasks) === 1 ? '' : 's' ?><?= $statusFilter !== '' || $priorityFilter !== '' || $search !== '' ? ' matching your filters' : '' ?>.</div>
    <div class="table-wrap"><table><thead><tr><th>Task</th><th>Room</th><th>Team member</th><th>Priority</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($tasks as $task): ?><tr><td><strong><?= e($task['title']) ?></strong><small class="cell-sub"><?= e($task['description'] ?: 'No additional notes') ?></small><?php if ((int) $task['checklist_count'] > 0): ?><small class="cell-sub">Checklist <?= (int) $task['checklist_done'] ?>/<?= (int) $task['checklist_count'] ?> complete</small><?php endif; ?></td><td><?= e($task['room_number'] ?? '—') ?></td><td><?= e($task['assigned_name'] ?? 'Unassigned') ?></td><td><span class="badge badge-<?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span></td><td><?= $task['due_date'] ? e(date('M j, Y', strtotime($task['due_date']))) : '—' ?></td><td><span class="badge badge-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></td><td><div class="task-actions"><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/checklist.php?id=<?= (int) $task['id'] ?>">Checklist</a><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $task['id'] ?>"><select name="status" aria-label="Task status"><?php foreach ($validStatuses as $s): ?><option value="<?= e($s) ?>" <?= $task['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></div></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
