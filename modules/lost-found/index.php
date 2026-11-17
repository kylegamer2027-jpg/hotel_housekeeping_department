<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
$pageTitle = 'Lost & found';
$activePage = 'lost-found';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['status_id'])) {
        $status = (string) ($_POST['status'] ?? '');
        if (in_array($status, ['stored','claimed','disposed'], true)) {
            $stmt = $pdo->prepare('UPDATE lost_found SET status=? WHERE id=?');
            $stmt->execute([$status, (int) $_POST['status_id']]);
            set_flash('Lost-and-found record updated.');
        }
    } else {
        $name = trim((string) ($_POST['item_name'] ?? ''));
        if ($name === '') {
            set_flash('Item name is required.', 'error');
        } else {
            $stmt = $pdo->prepare('INSERT INTO lost_found (item_name, description, room_number, found_by, date_found) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, trim((string) ($_POST['description'] ?? '')) ?: null, trim((string) ($_POST['room_number'] ?? '')) ?: null, trim((string) ($_POST['found_by'] ?? '')) ?: null, $_POST['date_found'] ?: date('Y-m-d')]);
            set_flash('Found item logged.');
        }
    }
    redirect('/hotel_housekeeping_department/modules/lost-found/index.php');
}
$items = $pdo->query('SELECT * FROM lost_found ORDER BY FIELD(status,"stored","claimed","disposed"), date_found DESC')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">KEEP EVERY ITEM SAFE</p><h2>Log a found item</h2></div></div><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Item name<input name="item_name" maxlength="180" required placeholder="What was found?"></label><label>Room number<input name="room_number" maxlength="20" placeholder="Where was it found?"></label><label>Found by<input name="found_by" maxlength="120" placeholder="Team member"></label><label>Date found<input type="date" name="date_found" value="<?= e(date('Y-m-d')) ?>"></label><label>Description<textarea name="description" placeholder="Color, brand, or other identifying details"></textarea></label><div class="form-actions"><button class="button button-primary">Log item</button></div></form></section>
<section class="panel"><div class="panel-heading"><h2>Found items register</h2></div><?php if (!$items): ?><div class="empty-state"><span>⌕</span><h3>No items logged</h3><p>Found items will be listed here for safe keeping.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Item</th><th>Room</th><th>Found by</th><th>Date</th><th>Status</th><th>Update</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><strong><?= e($item['item_name']) ?></strong><small class="cell-sub"><?= e($item['description'] ?? '') ?></small></td><td><?= e($item['room_number'] ?? '—') ?></td><td><?= e($item['found_by'] ?? '—') ?></td><td><?= e(date('M j, Y', strtotime($item['date_found']))) ?></td><td><span class="badge badge-<?= e($item['status']) ?>"><?= e(ucfirst($item['status'])) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="status_id" value="<?= (int) $item['id'] ?>"><select name="status"><?php foreach (['stored','claimed','disposed'] as $s): ?><option value="<?= $s ?>" <?= $item['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option><?php endforeach; ?></select><button class="button button-secondary button-small">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
