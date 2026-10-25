<?php
// includes/chat_sse.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
// CRITICAL: Release session lock so other scripts (like send_message.php) can use the session
session_write_close();

$other_id = isset($_GET['other_id']) ? (int)$_GET['other_id'] : null;

// Set time limit to infinity to keep the connection open
set_time_limit(0);

while (true) {
    if ($other_id) {
        // Specific chat between two users
        $stmt = $pdo->prepare("SELECT messages.*, users.username as sender_name 
                               FROM messages 
                               JOIN users ON messages.sender_id = users.id 
                               WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
                               AND messages.id > ? 
                               ORDER BY created_at ASC");
        $stmt->execute([$user_id, $other_id, $other_id, $user_id, $last_id]);
    } else {
        // All messages for this user (fallback)
        $stmt = $pdo->prepare("SELECT messages.*, users.username as sender_name 
                               FROM messages 
                               JOIN users ON messages.sender_id = users.id 
                               WHERE (receiver_id = ? OR sender_id = ?) 
                               AND messages.id > ? 
                               ORDER BY created_at ASC");
        $stmt->execute([$user_id, $user_id, $last_id]);
    }
    
    $messages = $stmt->fetchAll();

    if ($messages) {
        foreach ($messages as $msg) {
            echo "data: " . json_encode($msg) . "\n\n";
            $last_id = $msg['id'];
        }
        ob_flush();
        flush();
    }

    // Sleep for a bit to avoid overloading the server
    sleep(1);
    
    // Check if client disconnected
    if (connection_aborted()) break;
}
?>
