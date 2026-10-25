<?php
require_once 'auth_middleware.php';
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['other_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_id = is_numeric($_GET['other_id']) ? (int) $_GET['other_id'] : 0;
if ($other_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $other_id, $other_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
