<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role(['admin','supervisor']);
$pageTitle = 'Employees';
$activePage = 'employees';
$isAdmin = current_user()['role'] === 'admin';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? 'housekeeper');
    $validRoles = $isAdmin ? ['admin','supervisor','housekeeper'] : ['housekeeper'];
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || !in_array($role, $validRoles, true)) {
        set_flash('Enter a name, valid email, password with at least 10 characters, and a valid role.', 'error');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            set_flash('Team member account created.');
        } catch (PDOException $exception) {
            set_flash('Could not create account. That email may already be in use.', 'error');
        }
    }
    redirect('/hotel_housekeeping_department/modules/employees/index.php');
}
$employees = $pdo->query('SELECT u.id, u.name, u.email, u.role, u.created_at, (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to=u.id AND t.status!="completed") AS open_tasks FROM users u ORDER BY u.name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A CARING TEAM MAKES IT HAPPEN</p><h2>Add a team member</h2></div></div><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Full name<input name="name" maxlength="120" required></label><label>Email address<input name="email" type="email" maxlength="190" required></label><label>Temporary password<input name="password" type="password" minlength="10" required><small class="form-hint">At least 10 characters; share securely with the new team member.</small></label><label>Role<select name="role"><option value="housekeeper">Housekeeper</option><?php if ($isAdmin): ?><option value="supervisor">Supervisor</option><option value="admin">Administrator</option><?php endif; ?></select></label><div class="form-actions"><button class="button button-primary">Create account</button></div></form></section>
<section class="panel"><div class="toolbar"><h2>Our team</h2><div class="toolbar-actions"><?php if (in_array(current_user()['role'], ['admin','supervisor'], true)): ?><a class="button button-secondary" href="/hotel_housekeeping_department/modules/employees/shifts.php">Manage shifts</a><?php endif; ?><a class="button button-secondary" href="/hotel_housekeeping_department/modules/employees/workload.php">View workload</a></div></div><div class="table-wrap"><table><thead><tr><th>Team member</th><th>Role</th><th>Open tasks</th><th>Joined</th></tr></thead><tbody><?php foreach ($employees as $employee): ?><tr><td><strong><?= e($employee['name']) ?></strong><small class="cell-sub"><?= e($employee['email']) ?></small></td><td><?= e(ucfirst($employee['role'])) ?></td><td><?= (int) $employee['open_tasks'] ?></td><td><?= e(date('M j, Y', strtotime($employee['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
