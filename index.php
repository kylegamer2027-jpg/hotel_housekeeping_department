<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) {
    redirect('/hotel_housekeeping_department/dashboard.php');
}
redirect('/hotel_housekeeping_department/login.php');
