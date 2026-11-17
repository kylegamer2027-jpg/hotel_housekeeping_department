<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Create a task';
$activePage = 'tasks';
$rooms = $pdo->query('SELECT id, room_number FROM rooms ORDER BY room_number')->fetchAll();
$users = $pdo->query("SELECT u.id, u.name, COUNT(CASE WHEN t.status != 'completed' THEN 1 END) AS open_tasks FROM users u LEFT JOIN tasks t ON t.assigned_to=u.id GROUP BY u.id, u.name ORDER BY open_tasks, u.name")->fetchAll();
$taskTemplates = [
    'turnover-clean' => [
        'label' => 'Guest checkout · Full room clean',
        'title' => 'Prepare room after guest checkout',
        'description' => 'Complete a full turnover clean and prepare the room for its next guest.',
        'priority' => 'high',
        'checklist' => [
            'Remove used linen and towels',
            'Clean and sanitize bathroom surfaces',
            'Dust and wipe high-touch surfaces',
            'Make the bed with fresh linen',
            'Restock guest amenities',
            'Check the room and report any damage',
        ],
    ],
    'stayover-service' => [
        'label' => 'Stayover · Refresh room',
        'title' => 'Refresh occupied guest room',
        'description' => 'Refresh the room while keeping the guest’s belongings undisturbed.',
        'priority' => 'normal',
        'checklist' => [
            'Make the bed and tidy the room',
            'Replace used towels',
            'Empty waste bins',
            'Restock guest amenities',
            'Wipe bathroom surfaces',
        ],
    ],
    'deep-clean' => [
        'label' => 'Deep clean · Detailed room service',
        'title' => 'Complete a detailed room deep clean',
        'description' => 'Work through a detailed clean of the room and note any maintenance needs.',
        'priority' => 'high',
        'checklist' => [
            'Clean under and behind furniture',
            'Dust vents, lamps, and ledges',
            'Detail-clean bathroom fixtures and grout',
            'Spot-clean walls and doors',
            'Check curtains, upholstery, and soft furnishings',
            'Report maintenance or replacement needs',
        ],
    ],
    'amenity-restock' => [
        'label' => 'Guest amenities · Restock',
        'title' => 'Restock guest amenities',
        'description' => 'Check the room’s guest supplies and replenish items below the standard par level.',
        'priority' => 'normal',
        'checklist' => [
            'Check bathroom amenity levels',
            'Replenish drinking water and in-room refreshments',
            'Restock tea and coffee supplies',
            'Confirm items are clean and in date',
        ],
    ],
    'room-inspection' => [
        'label' => 'Inspection · Final room check',
        'title' => 'Complete final room inspection',
        'description' => 'Verify the room is clean, complete, and ready to welcome a guest.',
        'priority' => 'high',
        'checklist' => [
            'Check bed presentation and linen',
            'Check bathroom cleanliness and amenities',
            'Verify room supplies and guest information',
            'Test lights and visible room fixtures',
            'Record any issue that needs follow-up',
        ],
    ],
];
$error = '';
$selectedTemplateKey = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $postedTemplate = (string) ($_POST['template'] ?? '');
    $selectedTemplateKey = isset($taskTemplates[$postedTemplate]) ? $postedTemplate : '';
    $selectedTemplate = $selectedTemplateKey !== '' ? $taskTemplates[$selectedTemplateKey] : null;
    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '') {
        $error = 'Please enter a task title.';
    } else {
        $priority = in_array($_POST['priority'] ?? '', ['low','normal','high','urgent'], true) ? $_POST['priority'] : 'normal';
        $checklistItems = !empty($_POST['use_suggestions']) && $selectedTemplate
            ? $selectedTemplate['checklist']
            : [];
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, room_id, assigned_to, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $title,
            trim((string) ($_POST['description'] ?? '')) ?: null,
            ($_POST['room_id'] ?? '') !== '' ? (int) $_POST['room_id'] : null,
            ($_POST['assigned_to'] ?? '') !== '' ? (int) $_POST['assigned_to'] : null,
            $priority,
            ($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
        ]);
        $taskId = (int) $pdo->lastInsertId();
        if ($checklistItems) {
            $checklistInsert = $pdo->prepare('INSERT INTO task_checklist (task_id, item_text) VALUES (?, ?)');
            foreach ($checklistItems as $item) {
                $checklistInsert->execute([$taskId, $item]);
            }
        }
        $pdo->commit();
        set_flash('Your task has been added.');
        redirect('/hotel_housekeeping_department/modules/tasks/index.php');
    }
}
$selectedTemplate = $selectedTemplateKey !== '' ? $taskTemplates[$selectedTemplateKey] : null;
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">A THOUGHTFUL TO-DO</p><h2>Task details</h2></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="suggestion-picker"><label for="task-template">Start from a task suggestion<select id="task-template" name="template" data-task-template><option value="">Choose a suggested task (optional)</option><?php foreach ($taskTemplates as $key => $template): ?><option value="<?= e($key) ?>" data-title="<?= e($template['title']) ?>" data-description="<?= e($template['description']) ?>" data-priority="<?= e($template['priority']) ?>" data-checklist="<?= e(json_encode($template['checklist'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>" <?= $selectedTemplateKey === $key ? 'selected' : '' ?>><?= e($template['label']) ?></option><?php endforeach; ?></select></label><p>Suggestions prefill the task and offer a practical starter checklist. You can edit everything before saving.</p></div>
<div class="form-grid"><label>Task title<input id="task-title" name="title" maxlength="180" required value="<?= e($_POST['title'] ?? ($selectedTemplate['title'] ?? '')) ?>" placeholder="e.g. Refresh the guest suite"></label><label>Room<select name="room_id"><option value="">No room selected</option><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>" <?= (string) ($_POST['room_id'] ?? '') === (string) $room['id'] ? 'selected' : '' ?>><?= e($room['room_number']) ?></option><?php endforeach; ?></select></label>
<label>Assign to<select name="assigned_to"><option value="">Unassigned</option><?php foreach ($users as $user): ?><option value="<?= (int) $user['id'] ?>" <?= (string) ($_POST['assigned_to'] ?? '') === (string) $user['id'] ? 'selected' : '' ?>><?= e($user['name']) ?> · <?= (int) $user['open_tasks'] ?> open task<?= (int) $user['open_tasks'] === 1 ? '' : 's' ?></option><?php endforeach; ?></select><small class="form-hint">Team members with fewer open tasks are suggested first.</small></label><label>Priority<select id="task-priority" name="priority"><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= $priority ?>" <?= ($_POST['priority'] ?? ($selectedTemplate['priority'] ?? 'normal')) === $priority ? 'selected' : '' ?>><?= e(ucfirst($priority)) ?></option><?php endforeach; ?></select></label><label>Due date<input type="date" name="due_date" value="<?= e($_POST['due_date'] ?? '') ?>"></label><label>Notes<textarea id="task-description" name="description" placeholder="Add helpful details for the team"><?= e($_POST['description'] ?? ($selectedTemplate['description'] ?? '')) ?></textarea></label></div>
<div class="suggestion-checklist" data-suggestion-preview hidden><label class="suggestion-toggle"><input type="checkbox" name="use_suggestions" value="1" <?= $_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($_POST['use_suggestions']) ? 'checked' : '' ?>><span><strong>Add suggested checklist items</strong><small>They’ll be saved with the task and can be edited later.</small></span></label><ol data-suggestion-list aria-live="polite"></ol></div>
<div class="form-actions"><button class="button button-primary">Create task</button><a class="button button-secondary" href="/hotel_housekeeping_department/modules/tasks/index.php">Cancel</a></div></form></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
