<?php
$pageTitle = $pageTitle ?? 'Housekeeping';
$activePage = $activePage ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Housekeeping</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hotel_housekeeping_department/assets/css/style.css?v=3">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <?php require __DIR__ . '/navbar.php'; ?>
        <div class="page-content">
            <?php $flash = take_flash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
            <?php endif; ?>
