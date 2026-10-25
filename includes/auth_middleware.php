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
        case 'employer':
            return $base . '/employer/dashboard.php';
        case 'employee':
            return $base . '/employee/dashboard.php';
        default:
            return loginUrl();
    }
}

function checkRole($allowedRoles) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . loginUrl());
        exit();
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        header("Location: " . dashboardUrlByRole($_SESSION['role'] ?? ''));
        exit();
    }
}
?>
