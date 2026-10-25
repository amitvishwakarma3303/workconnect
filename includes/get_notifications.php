<?php
// includes/get_notifications.php
require_once 'db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get unread count
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt_count->execute([$user_id]);
    $unread_count = $stmt_count->fetchColumn();

    // Get recent notifications
    $stmt_recent = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt_recent->execute([$user_id]);
    $notifications = $stmt_recent->fetchAll();

    echo json_encode([
        'success' => true, 
        'unread_count' => $unread_count,
        'notifications' => $notifications
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to load notifications']);
}
?>
