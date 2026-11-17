<?php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
redirect('/hotel_housekeeping_department/login.php');
