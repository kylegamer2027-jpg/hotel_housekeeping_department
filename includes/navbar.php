<?php $user = current_user(); ?>
<header class="topbar">
    <div>
        <p class="eyebrow"><?= e(date('l, F j, Y')) ?></p>
        <h1><?= e($pageTitle) ?></h1>
    </div>
    <div class="user-menu">
        <div class="avatar"><?= e(strtoupper(substr($user['name'] ?: 'U', 0, 1))) ?></div>
        <div class="user-meta"><strong><?= e($user['name']) ?></strong><small><?= e(ucfirst($user['role'])) ?></small></div>
        <a class="logout-link" href="/hotel_housekeeping_department/logout.php">Sign out</a>
    </div>
</header>
