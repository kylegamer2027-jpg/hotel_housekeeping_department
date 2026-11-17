<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'Good morning';
$activePage = 'dashboard';
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$pageTitle = $greeting;
$counts = [
    'tasks' => (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'completed'")->fetchColumn(),
    'today' => (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'completed' AND due_date = CURDATE()")->fetchColumn(),
    'overdue' => (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'completed' AND due_date < CURDATE()")->fetchColumn(),
    'ready' => (int) $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'ready'")->fetchColumn(),
    'rooms' => (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
    'maintenance' => (int) $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status != 'completed'")->fetchColumn(),
    'low_stock' => (int) $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE quantity <= reorder_level")->fetchColumn(),
];
$tasks = $pdo->query("SELECT t.id, t.title, t.status, t.priority, t.due_date, r.room_number, u.name AS assigned_name FROM tasks t LEFT JOIN rooms r ON r.id = t.room_id LEFT JOIN users u ON u.id = t.assigned_to WHERE t.status != 'completed' ORDER BY t.due_date IS NULL, t.due_date, t.id DESC LIMIT 6")->fetchAll();
$roomStatuses = $pdo->query('SELECT status, COUNT(*) AS total FROM rooms GROUP BY status')->fetchAll();
$roomStatusCounts = array_column($roomStatuses, 'total', 'status');
$readiness = $counts['rooms'] > 0 ? (int) round(($counts['ready'] / $counts['rooms']) * 100) : 0;
$lowStockItems = $pdo->query('SELECT item_name, category, quantity, unit, reorder_level FROM inventory_items WHERE quantity <= reorder_level ORDER BY (quantity = 0) DESC, item_name LIMIT 5')->fetchAll();
$priorityTasks = $pdo->query("SELECT t.id, t.title, t.priority, t.due_date, r.room_number FROM tasks t LEFT JOIN rooms r ON r.id=t.room_id WHERE t.status!='completed' AND t.priority IN ('urgent','high') ORDER BY FIELD(t.priority,'urgent','high'), t.due_date IS NULL, t.due_date LIMIT 4")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-banner"><div><p class="eyebrow"><?= e(date('l, F j')) ?> · YOUR DAY, IN GOOD ORDER</p><h2><?= e($greeting) ?>, <?= e(explode(' ', current_user()['name'])[0]) ?>.</h2><p>Make room for a lovely stay. Here’s the team's daily pulse.</p></div><div class="banner-actions"><a class="button button-light" href="/hotel_housekeeping_department/modules/rooms/index.php">＋ Add a room</a><a class="button button-primary" href="/hotel_housekeeping_department/modules/tasks/create.php">＋ New task</a></div><span class="banner-flower">✿</span></section>
<?php if ($counts['overdue'] > 0): ?><div class="alert alert-warning"><strong><?= $counts['overdue'] ?> overdue task<?= $counts['overdue'] === 1 ? '' : 's' ?></strong> need follow-up. <a class="text-link" href="/hotel_housekeeping_department/modules/tasks/index.php">Review open tasks →</a></div><?php endif; ?>
<section class="stats-grid">
    <a class="stat-card stat-link" href="/hotel_housekeeping_department/modules/tasks/index.php"><span class="stat-icon pink">✓</span><div><p>Open tasks</p><strong><?= $counts['tasks'] ?></strong></div><span class="stat-foot"><?= $counts['today'] ?> due today · <?= $counts['overdue'] ?> overdue</span></a>
    <a class="stat-card stat-link" href="/hotel_housekeeping_department/modules/rooms/readiness.php"><span class="stat-icon khaki">⌂</span><div><p>Rooms ready</p><strong><?= $counts['ready'] ?><small> / <?= $counts['rooms'] ?></small></strong></div><span class="stat-foot"><?= $readiness ?>% of rooms guest-ready</span></a>
    <a class="stat-card stat-link" href="/hotel_housekeeping_department/modules/maintenance/index.php"><span class="stat-icon cream">⚒</span><div><p>Maintenance</p><strong><?= $counts['maintenance'] ?></strong></div><span class="stat-foot">Requests to follow up</span></a>
    <a class="stat-card stat-link" href="/hotel_housekeeping_department/modules/inventory/linen.php"><span class="stat-icon sage">▤</span><div><p>Low stock items</p><strong><?= $counts['low_stock'] ?></strong></div><span class="stat-foot">At or below reorder point</span></a>
</section>
<div class="dashboard-grid">
<section class="panel dashboard-main">
    <div class="panel-heading"><div><p class="eyebrow">THE DAILY EDIT</p><h2>Tasks to keep an eye on</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/tasks/index.php">All tasks →</a></div>
    <?php if (!$tasks): ?><div class="empty-state"><span>✿</span><h3>All caught up</h3><p>There are no open tasks right now.</p></div>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>Task</th><th>Room</th><th>Assigned to</th><th>Due</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($tasks as $task): ?><tr><td><strong><?= e($task['title']) ?></strong><small class="cell-sub">#<?= (int) $task['id'] ?></small></td><td><?= $task['room_number'] ? e($task['room_number']) : '—' ?></td><td><?= e($task['assigned_name'] ?? 'Unassigned') ?></td><td><?php if ($task['due_date']): ?><span class="<?= $task['due_date'] < date('Y-m-d') ? 'overdue-date' : '' ?>"><?= e(date('M j', strtotime($task['due_date']))) ?></span><?php else: ?>—<?php endif; ?></td><td><span class="badge badge-<?= e($task['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<aside class="dashboard-side">
    <section class="panel readiness-panel"><div class="panel-heading"><div><p class="eyebrow">ROOM TURNOVER</p><h2>Ready to welcome</h2></div><span class="readiness-number"><?= $readiness ?>%</span></div><div class="progress-track"><span style="width:<?= $readiness ?>%"></span></div><div class="room-legend"><?php foreach (['dirty' => 'Dirty', 'in_progress' => 'In progress', 'inspected' => 'Inspected', 'ready' => 'Ready'] as $status => $label): ?><span><i class="legend-dot dot-<?= e($status) ?>"></i><?= e($label) ?><strong><?= (int) ($roomStatusCounts[$status] ?? 0) ?></strong></span><?php endforeach; ?></div><a class="text-link" href="/hotel_housekeeping_department/modules/rooms/index.php">Manage rooms →</a></section>
    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">PRIORITY CARE</p><h2>Needs attention</h2></div></div><?php if (!$priorityTasks): ?><div class="compact-empty">No high-priority tasks right now.</div><?php else: ?><div class="priority-list"><?php foreach ($priorityTasks as $task): ?><a href="/hotel_housekeeping_department/modules/tasks/checklist.php?id=<?= (int) $task['id'] ?>" class="priority-item"><span class="priority-mark priority-<?= e($task['priority']) ?>">!</span><span><strong><?= e($task['title']) ?></strong><small>Room <?= e($task['room_number'] ?? '—') ?> · <?= e(ucfirst($task['priority'])) ?></small></span></a><?php endforeach; ?></div><?php endif; ?></section>
    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">STOCK WATCH</p><h2>Restock reminders</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/inventory/linen.php">Inventory →</a></div><?php if (!$lowStockItems): ?><div class="compact-empty">Everything is stocked above its reorder point.</div><?php else: ?><div class="stock-list"><?php foreach ($lowStockItems as $item): ?><div class="stock-item"><span><strong><?= e($item['item_name']) ?></strong><small><?= e(ucfirst($item['category'])) ?></small></span><span class="stock-quantity <?= (int) $item['quantity'] === 0 ? 'stock-empty' : '' ?>"><?= (int) $item['quantity'] ?> <?= e($item['unit']) ?></span></div><?php endforeach; ?></div><?php endif; ?></section>
</aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
