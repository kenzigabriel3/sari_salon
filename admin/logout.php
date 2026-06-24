<?php
include_once '../includes/db.php';

// Only clear ADMIN session keys — user session remains untouched
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Redirect to admin login
header("Location: login.php");
exit();
