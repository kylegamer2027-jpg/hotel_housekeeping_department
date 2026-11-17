<aside class="sidebar">
    <a class="brand" href="/hotel_housekeeping_department/dashboard.php">
        <span class="brand-mark">H</span>
        <span><strong>Hearth & Haven</strong><small>HOUSEKEEPING</small></span>
    </a>
    <nav class="side-nav" aria-label="Main navigation">
        <span class="nav-label">WORKSPACE</span>
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="/hotel_housekeeping_department/dashboard.php"><span>◫</span> Overview</a>
        <a class="<?= $activePage === 'tasks' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/tasks/index.php"><span>✓</span> Housekeeping tasks</a>
        <a class="<?= $activePage === 'rooms' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/rooms/index.php"><span>⌂</span> Rooms</a>
        <span class="nav-label">OPERATIONS</span>
        <a class="<?= $activePage === 'inventory' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/inventory/linen.php"><span>▤</span> Inventory</a>
        <a class="<?= $activePage === 'lost-found' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/lost-found/index.php"><span>⌕</span> Lost & found</a>
        <a class="<?= $activePage === 'maintenance' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/maintenance/index.php"><span>⚒</span> Maintenance</a>
        <span class="nav-label">PEOPLE & INSIGHTS</span>
        <a class="<?= $activePage === 'employees' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/employees/index.php"><span>♙</span> Employees</a>
        <a class="<?= $activePage === 'reports' ? 'active' : '' ?>" href="/hotel_housekeeping_department/modules/reports/index.php"><span>▥</span> Reports</a>
    </nav>
    <div class="sidebar-note"><span class="note-icon">✿</span><strong>A little care goes a long way.</strong><p>Thoughtful details make every stay feel like home.</p></div>
</aside>
