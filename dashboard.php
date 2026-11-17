<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'Good morning';
$activePage = 'dashboard';
$counts = [
    'tasks' => (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'completed'")->fetchColumn(),
    'ready' => (int) $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'ready'")->fetchColumn(),
    'rooms' => (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
    'maintenance' => (int) $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status != 'completed'")->fetchColumn(),
];
$tasks = $pdo->query("SELECT t.id, t.title, t.status, t.priority, t.due_date, r.room_number, u.name AS assigned_name FROM tasks t LEFT JOIN rooms r ON r.id = t.room_id LEFT JOIN users u ON u.id = t.assigned_to WHERE t.status != 'completed' ORDER BY t.due_date IS NULL, t.due_date, t.id DESC LIMIT 6")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-banner"><div><p class="eyebrow">YOUR DAY, IN GOOD ORDER</p><h2>Make room for a lovely stay.</h2><p>Here’s what’s happening across the housekeeping team.</p></div><a class="button button-light" href="/hotel_housekeeping_department/modules/tasks/create.php">＋ New task</a><span class="banner-flower">✿</span></section>
<section class="stats-grid">
    <article class="stat-card"><span class="stat-icon pink">✓</span><div><p>Open tasks</p><strong><?= $counts['tasks'] ?></strong></div><span class="stat-foot">Needs a little attention</span></article>
    <article class="stat-card"><span class="stat-icon khaki">⌂</span><div><p>Rooms ready</p><strong><?= $counts['ready'] ?><small> / <?= $counts['rooms'] ?></small></strong></div><span class="stat-foot">Ready for your guests</span></article>
    <article class="stat-card"><span class="stat-icon cream">⚒</span><div><p>Maintenance</p><strong><?= $counts['maintenance'] ?></strong></div><span class="stat-foot">Requests in progress</span></article>
    <article class="stat-card"><span class="stat-icon sage">♙</span><div><p>Team members</p><strong><?= (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?></strong></div><span class="stat-foot">Caring for every detail</span></article>
</section>
<section class="panel">
    <div class="panel-heading"><div><p class="eyebrow">THE DAILY EDIT</p><h2>Tasks to keep an eye on</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/index.php">All tasks →</a></div>
    <?php if (!$tasks): ?><div class="empty-state"><span>✿</span><h3>All caught up</h3><p>There are no open tasks right now.</p></div>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>Task</th><th>Room</th><th>Assigned to</th><th>Due</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($tasks as $task): ?><tr><td><strong><?= e($task['title']) ?></strong><small class="cell-sub">#<?= (int) $task['id'] ?></small></td><td><?= $task['room_number'] ? e($task['room_number']) : '—' ?></td><td><?= e($task['assigned_name'] ?? 'Unassigned') ?></td><td><?= $task['due_date'] ? e(date('M j', strtotime($task['due_date']))) : '—' ?></td><td><span class="badge badge-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
