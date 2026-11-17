<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Reports';
$activePage = 'reports';
$taskStats = $pdo->query("SELECT status, COUNT(*) AS total FROM tasks GROUP BY status")->fetchAll();
$roomStats = $pdo->query('SELECT status, COUNT(*) AS total FROM rooms GROUP BY status ORDER BY status')->fetchAll();
$lowStock = $pdo->query('SELECT category, item_name, quantity, unit, reorder_level FROM inventory_items WHERE quantity <= reorder_level ORDER BY category, item_name')->fetchAll();
$maintenanceCount = (int) $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status!='completed'")->fetchColumn();
$lostCount = (int) $pdo->query("SELECT COUNT(*) FROM lost_found WHERE status='stored'")->fetchColumn();
$taskMap = array_column($taskStats, 'total', 'status');
$roomMap = array_column($roomStats, 'total', 'status');
require __DIR__ . '/../../includes/header.php';
?>
<section class="stats-grid"><article class="stat-card"><span class="stat-icon pink">✓</span><div><p>Tasks completed</p><strong><?= (int) ($taskMap['completed'] ?? 0) ?></strong></div><span class="stat-foot">All recorded tasks</span></article><article class="stat-card"><span class="stat-icon khaki">⌂</span><div><p>Rooms ready</p><strong><?= (int) ($roomMap['ready'] ?? 0) ?></strong></div><span class="stat-foot">Guest-ready rooms</span></article><article class="stat-card"><span class="stat-icon cream">⚒</span><div><p>Open maintenance</p><strong><?= $maintenanceCount ?></strong></div><span class="stat-foot">Needs follow-up</span></article><article class="stat-card"><span class="stat-icon sage">⌕</span><div><p>Items in storage</p><strong><?= $lostCount ?></strong></div><span class="stat-foot">Awaiting a match</span></article></section>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A GENTLE PULSE CHECK</p><h2>Housekeeping at a glance</h2></div></div><div class="form-grid"><div><h3 class="section-title">Task status</h3><?php foreach (['pending','in_progress','completed'] as $status): ?><p><span class="badge badge-<?= $status ?>"><?= e(ucwords(str_replace('_',' ', $status))) ?></span> &nbsp; <?= (int) ($taskMap[$status] ?? 0) ?></p><?php endforeach; ?></div><div><h3 class="section-title">Room status</h3><?php foreach (['dirty','in_progress','inspected','ready','out_of_order'] as $status): ?><p><span class="badge badge-<?= $status ?>"><?= e(ucwords(str_replace('_',' ', $status))) ?></span> &nbsp; <?= (int) ($roomMap[$status] ?? 0) ?></p><?php endforeach; ?></div></div></section>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">RESTOCK BEFORE IT RUNS OUT</p><h2>Items below their reorder point</h2></div></div><?php if (!$lowStock): ?><div class="empty-state"><span>✿</span><h3>Stock looks good</h3><p>No items are currently at or below their reorder level.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Category</th><th>Item</th><th>On hand</th><th>Reorder point</th></tr></thead><tbody><?php foreach ($lowStock as $item): ?><tr><td><?= e(ucfirst($item['category'])) ?></td><td><strong><?= e($item['item_name']) ?></strong></td><td><span class="badge badge-urgent"><?= (int) $item['quantity'] ?> <?= e($item['unit']) ?></span></td><td><?= (int) $item['reorder_level'] ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
