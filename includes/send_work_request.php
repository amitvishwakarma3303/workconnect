<?php
// includes/send_work_request.php
require_once 'db_connect.php';
require_once 'csrf.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please sign in to send a work request.']);
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'user') {
    echo json_encode(['success' => false, 'error' => 'Only clients/users can send work requests to workers.']);
    exit();
}

if (!csrf_validate(csrf_token_from_request() ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired security token. Please refresh and try again.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$worker_id = isset($data['worker_id']) && is_numeric($data['worker_id']) ? (int)$data['worker_id'] : 0;
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$location = trim($data['location'] ?? '');
$work_date = !empty($data['work_date']) ? $data['work_date'] : null;
$budget = trim($data['budget'] ?? '');
$category_id = isset($data['category_id']) && is_numeric($data['category_id']) ? (int)$data['category_id'] : null;

if ($worker_id <= 0 || empty($title) || empty($description) || empty($location)) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all required fields (Task title, description, and location).']);
    exit();
}

if ($worker_id === $user_id) {
    echo json_encode(['success' => false, 'error' => 'You cannot hire yourself.']);
    exit();
}

try {
    // Verify worker exists
    $stmtWk = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmtWk->execute([$worker_id]);
    $worker = $stmtWk->fetch();
    if (!$worker) {
        echo json_encode(['success' => false, 'error' => 'Worker not found.']);
        exit();
    }

    // Insert work request
    $stmt = $pdo->prepare("
        INSERT INTO work_requests (user_id, worker_id, category_id, title, description, location, work_date, budget, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$user_id, $worker_id, $category_id, $title, $description, $location, $work_date, $budget]);
    $requestId = (int)$pdo->lastInsertId();

    // Send notification to the worker
    $notifyMsg = "New Work Request received from " . ($_SESSION['username'] ?? 'a client') . ": " . $title . " (" . $location . ")";
    $stmtNotify = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'application')");
    $stmtNotify->execute([$worker_id, $notifyMsg]);

    echo json_encode(['success' => true, 'message' => 'Work request sent successfully to ' . $worker['username'] . '!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to send work request: ' . $e->getMessage()]);
}
?>
