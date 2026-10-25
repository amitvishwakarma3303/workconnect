<?php
// includes/handle_work_request.php
require_once 'db_connect.php';
require_once 'csrf.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please sign in to update requests.']);
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'worker') {
    echo json_encode(['success' => false, 'error' => 'Only workers can respond to incoming work requests.']);
    exit();
}

if (!csrf_validate(csrf_token_from_request() ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired security token. Please refresh and try again.']);
    exit();
}

$worker_id = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$request_id = isset($data['request_id']) && is_numeric($data['request_id']) ? (int)$data['request_id'] : 0;
$action = $data['action'] ?? ''; // 'accept', 'reject', 'complete'

$validActions = ['accept' => 'accepted', 'reject' => 'rejected', 'complete' => 'completed'];

if ($request_id <= 0 || !isset($validActions[$action])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request parameters.']);
    exit();
}

$newStatus = $validActions[$action];

try {
    // Check request ownership
    $stmt = $pdo->prepare("SELECT * FROM work_requests WHERE id = ? AND worker_id = ?");
    $stmt->execute([$request_id, $worker_id]);
    $request = $stmt->fetch();

    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Work request not found or access denied.']);
        exit();
    }

    // Update status
    $updateStmt = $pdo->prepare("UPDATE work_requests SET status = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $request_id]);

    // Send notification to the user
    $workerName = $_SESSION['username'] ?? 'The worker';
    if ($action === 'accept') {
        $msg = "Good news! " . $workerName . " accepted your work request for: " . $request['title'];
        $type = 'hiring';
    } elseif ($action === 'complete') {
        $msg = $workerName . " has marked your task '" . $request['title'] . "' as completed.";
        $type = 'hiring';
    } else {
        $msg = $workerName . " was unable to accept your work request for: " . $request['title'];
        $type = 'rejection';
    }

    $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $notifyStmt->execute([$request['user_id'], $msg, $type]);

    echo json_encode(['success' => true, 'message' => 'Work request marked as ' . $newStatus . '.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to update request: ' . $e->getMessage()]);
}
?>
