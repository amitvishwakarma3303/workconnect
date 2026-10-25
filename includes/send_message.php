<?php
// includes/send_message.php
require_once 'db_connect.php';
require_once 'csrf.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}
if (!csrf_validate(csrf_token_from_request() ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid request token']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's JSON or Form data
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $receiver_id = $data['receiver_id'] ?? null;
        $message = $data['message'] ?? '';
    } else {
        $receiver_id = $_POST['receiver_id'] ?? null;
        $message = $_POST['message'] ?? '';
    }
    
    $sender_id = (int) $_SESSION['user_id'];
    $receiver_id = is_numeric($receiver_id) ? (int) $receiver_id : null;

    if ($receiver_id && !empty($message)) {
        try {
            $stmt_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt_user->execute([$receiver_id]);
            if (!$stmt_user->fetchColumn()) {
                echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $message]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
}
?>
