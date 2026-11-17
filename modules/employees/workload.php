<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Team workload';
$activePage = 'employees';
$workload = $pdo->query("SELECT u.id, u.name, u.role, COUNT(CASE WHEN t.status!='completed' THEN 1 END) AS open_tasks, COUNT(CASE WHEN t.status='completed' THEN 1 END) AS completed_tasks FROM users u LEFT JOIN tasks t ON t.assigned_to=u.id GROUP BY u.id, u.name, u.role ORDER BY open_tasks DESC, u.name")->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A BALANCED DAY FOR EVERYONE</p><h2>Team workload</h2></div><a class="text-link" href="/hotel_housekeeping_department/modules/employees/index.php">← Team</a></div><?php if (!$workload): ?><div class="empty-state"><span>♙</span><h3>No team members</h3><p>Team members will appear here when added.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Team member</th><th>Role</th><th>Open tasks</th><th>Completed tasks</th><th>Quick look</th></tr></thead><tbody><?php foreach ($workload as $member): ?><tr><td><strong><?= e($member['name']) ?></strong></td><td><?= e(ucfirst($member['role'])) ?></td><td><?= (int) $member['open_tasks'] ?></td><td><?= (int) $member['completed_tasks'] ?></td><td><?php for ($i=0; $i < min(10, (int) $member['open_tasks']); $i++): ?><span style="color:var(--pink)">●</span><?php endfor; ?><?php if ((int) $member['open_tasks'] === 0): ?><span class="muted">Available for a task</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
