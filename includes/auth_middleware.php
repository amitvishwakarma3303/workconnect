<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function appBaseUrl() {
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $project = basename(dirname(__DIR__));
    $base = '/' . $project;
    return $base;
}

function loginUrl() {
    return appBaseUrl() . '/login.php';
}

function dashboardUrlByRole($role) {
    $base = appBaseUrl();
    switch ($role) {
        case 'admin':
            return $base . '/admin/dashboard.php';
        case 'user':
            return $base . '/user/dashboard.php';
        case 'worker':
            return $base . '/worker/dashboard.php';
        default:
            return loginUrl();
    }
}

function checkRole($allowedRoles) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . loginUrl());
        exit();
    }
    $currentRole = $_SESSION['role'] ?? '';
    if (!in_array($currentRole, $allowedRoles, true)) {
        header("Location: " . dashboardUrlByRole($currentRole));
        exit();
    }
}
?>
