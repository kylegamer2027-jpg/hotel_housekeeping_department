<?php
require_once __DIR__ . '/auth.php';
require_login();
$activePage = 'inventory';
$categoryTitles = ['linen' => 'Linen inventory', 'amenities' => 'Guest amenities', 'supplies' => 'Cleaning supplies'];
$categoryLabels = ['linen' => 'Linen', 'amenities' => 'Amenities', 'supplies' => 'Supplies'];
$itemSuggestions = [
    'linen' => ['Bath towel', 'Hand towel', 'Bath mat', 'Bed sheet', 'Pillowcase', 'Duvet cover', 'Blanket', 'Bathrobe'],
    'amenities' => ['Shampoo', 'Conditioner', 'Body wash', 'Soap', 'Toothbrush kit', 'Shower cap', 'Vanity kit', 'Water bottle', 'Tea bags', 'Coffee sachet'],
    'supplies' => ['All-purpose cleaner', 'Glass cleaner', 'Disinfectant', 'Floor cleaner', 'Microfiber cloth', 'Rubber gloves', 'Trash bags', 'Toilet paper', 'Paper towels', 'Sponge'],
];
$unitSuggestions = [
    'linen' => ['pieces', 'sets', 'pairs', 'bags'],
    'amenities' => ['pieces', 'bottles', 'packs', 'boxes'],
    'supplies' => ['bottles', 'liters', 'packs', 'boxes', 'pieces', 'rolls'],
];
if (!isset($categoryTitles[$inventoryCategory])) {
    http_response_code(400);
    exit('Unknown inventory category.');
}
$pageTitle = $categoryTitles[$inventoryCategory];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM inventory_items WHERE id=? AND category=?');
        $stmt->execute([(int) $_POST['delete_id'], $inventoryCategory]);
        set_flash('Inventory item removed.');
    } elseif (isset($_POST['update_id'])) {
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        $reorder = filter_var($_POST['reorder_level'] ?? null, FILTER_VALIDATE_INT);
        if ($quantity !== false && $quantity >= 0 && $reorder !== false && $reorder >= 0) {
            $stmt = $pdo->prepare('UPDATE inventory_items SET quantity=?, reorder_level=? WHERE id=? AND category=?');
            $stmt->execute([$quantity, $reorder, (int) $_POST['update_id'], $inventoryCategory]);
            set_flash('Stock levels updated.');
        } else {
            set_flash('Quantities must be zero or greater.', 'error');
        }
    } else {
        $name = trim((string) ($_POST['item_name'] ?? ''));
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
        $reorder = filter_var($_POST['reorder_level'] ?? null, FILTER_VALIDATE_INT);
        if ($name !== '' && $quantity !== false && $quantity >= 0 && $reorder !== false && $reorder >= 0) {
            $stmt = $pdo->prepare('INSERT INTO inventory_items (category, item_name, quantity, unit, reorder_level) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$inventoryCategory, $name, $quantity, trim((string) ($_POST['unit'] ?? 'units')) ?: 'units', $reorder]);
            set_flash('Inventory item added.');
        } else {
            set_flash('Enter an item name and valid non-negative quantities.', 'error');
        }
    }
    redirect('/hotel_housekeeping_department/modules/inventory/' . $inventoryCategory . '.php');
}
$stmt = $pdo->prepare('SELECT * FROM inventory_items WHERE category=? ORDER BY item_name');
$stmt->execute([$inventoryCategory]);
$items = $stmt->fetchAll();
require __DIR__ . '/header.php';
?>
<section class="panel">
    <div class="toolbar"><h2><?= e($categoryLabels[$inventoryCategory]) ?> at a glance</h2><div class="toolbar-actions"><a class="button button-secondary" href="/hotel_housekeeping_department/modules/inventory/linen.php">Linen</a><a class="button button-secondary" href="/hotel_housekeeping_department/modules/inventory/amenities.php">Amenities</a><a class="button button-secondary" href="/hotel_housekeeping_department/modules/inventory/supplies.php">Supplies</a></div></div>
    <form method="post" class="toolbar"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input name="item_name" list="inventory-item-suggestions" required maxlength="150" placeholder="Item name"><datalist id="inventory-item-suggestions"><?php foreach ($itemSuggestions[$inventoryCategory] as $suggestion): ?><option value="<?= e($suggestion) ?>"><?php endforeach; ?></datalist><input type="number" name="quantity" min="0" required placeholder="Quantity" style="max-width:120px"><input name="unit" list="inventory-unit-suggestions" maxlength="40" placeholder="Unit (e.g. pieces)" style="max-width:160px"><datalist id="inventory-unit-suggestions"><?php foreach ($unitSuggestions[$inventoryCategory] as $unitSuggestion): ?><option value="<?= e($unitSuggestion) ?>"><?php endforeach; ?></datalist><input type="number" name="reorder_level" min="0" value="10" required placeholder="Reorder at" style="max-width:120px"><button class="button button-primary">＋ Add item</button></form>
    <?php if (!$items): ?><div class="empty-state"><span>▤</span><h3>No <?= e(strtolower($categoryLabels[$inventoryCategory])) ?> tracked yet</h3><p>Add an item above to keep stock organized.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Item</th><th>On hand</th><th>Reorder point</th><th>Stock</th><th></th></tr></thead><tbody><?php foreach ($items as $item): $formId = 'stock-' . (int) $item['id']; ?><tr><td><form id="<?= e($formId) ?>" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="update_id" value="<?= (int) $item['id'] ?>"></form><strong><?= e($item['item_name']) ?></strong></td><td><input form="<?= e($formId) ?>" type="number" min="0" name="quantity" value="<?= (int) $item['quantity'] ?>" aria-label="Quantity" style="max-width:85px"> <?= e($item['unit']) ?></td><td><input form="<?= e($formId) ?>" type="number" min="0" name="reorder_level" value="<?= (int) $item['reorder_level'] ?>" aria-label="Reorder level" style="max-width:85px"></td><td><?php if ((int) $item['quantity'] <= (int) $item['reorder_level']): ?><span class="badge badge-urgent">Reorder</span><?php else: ?><span class="badge badge-ready">In stock</span><?php endif; ?></td><td><button form="<?= e($formId) ?>" class="button button-secondary button-small">Save</button> <form method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int) $item['id'] ?>"><button class="button button-danger button-small" data-confirm="Remove this inventory item?">×</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
