<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Create a task';
$activePage = 'tasks';
$rooms = $pdo->query('SELECT id, room_number FROM rooms ORDER BY room_number')->fetchAll();
$users = $pdo->query('SELECT id, name FROM users ORDER BY name')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '') {
        $error = 'Please enter a task title.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, room_id, assigned_to, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $title,
            trim((string) ($_POST['description'] ?? '')) ?: null,
            ($_POST['room_id'] ?? '') !== '' ? (int) $_POST['room_id'] : null,
            ($_POST['assigned_to'] ?? '') !== '' ? (int) $_POST['assigned_to'] : null,
            in_array($_POST['priority'] ?? '', ['low','normal','high','urgent'], true) ? $_POST['priority'] : 'normal',
            ($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
        ]);
        set_flash('Your task has been added.');
        redirect('/hotel_housekeeping_department/modules/tasks/index.php');
    }
}
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A THOUGHTFUL TO-DO</p><h2>Task details</h2></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="form-grid"><label>Task title<input name="title" maxlength="180" required value="<?= e($_POST['title'] ?? '') ?>" placeholder="e.g. Refresh the guest suite"></label><label>Room<select name="room_id"><option value="">No room selected</option><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>"><?= e($room['room_number']) ?></option><?php endforeach; ?></select></label>
<label>Assign to<select name="assigned_to"><option value="">Unassigned</option><?php foreach ($users as $user): ?><option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?></option><?php endforeach; ?></select></label><label>Priority<select name="priority"><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= $priority ?>" <?= ($_POST['priority'] ?? 'normal') === $priority ? 'selected' : '' ?>><?= e(ucfirst($priority)) ?></option><?php endforeach; ?></select></label><label>Due date<input type="date" name="due_date" value="<?= e($_POST['due_date'] ?? '') ?>"></label><label>Notes<textarea name="description" placeholder="Add helpful details for the team"><?= e($_POST['description'] ?? '') ?></textarea></label></div>
<div class="form-actions"><button class="button button-primary">Create task</button><a class="button button-secondary" href="/hotel_housekeeping_department/modules/tasks/index.php">Cancel</a></div></form></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
