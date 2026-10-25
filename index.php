<?php
// index.php - Entry point for Work Connect - Worker Job Portal
require_once 'includes/auth_middleware.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: " . dashboardUrlByRole($_SESSION['role']));
    exit();
}

header("Location: home.html");
exit();
?>
