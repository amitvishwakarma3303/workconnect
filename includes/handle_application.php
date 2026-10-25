<?php
// includes/handle_application.php
require_once 'db_connect.php';
require_once 'csrf.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}
if (!csrf_validate(csrf_token_from_request() ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid request token']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$application_id = isset($data['application_id']) && is_numeric($data['application_id']) ? (int) $data['application_id'] : null;
$action = $data['action'] ?? null; // 'accept' or 'reject'

if (!$application_id || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

try {
    // Verify ownership
    $stmt_verify = $pdo->prepare("
        SELECT ja.*, j.title, j.employer_id 
        FROM job_applications ja 
        JOIN jobs j ON ja.job_id = j.id 
        WHERE ja.id = ?
    ");
    $stmt_verify->execute([$application_id]);
    $app = $stmt_verify->fetch();

    if (!$app || $app['employer_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Application not found or access denied']);
        exit();
    }

    $status = ($action === 'accept') ? 'accepted' : 'rejected';
    
    // Update application status
    $stmt_update = $pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
    $stmt_update->execute([$status, $application_id]);

    // Notify employee
    $msg = ($action === 'accept') 
        ? "Congratulations! You have been hired for the position: " . $app['title']
        : "Your application for " . $app['title'] . " was not selected.";
    
    $type = ($action === 'accept') ? 'hiring' : 'rejection';
    
    $stmt_notify = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt_notify->execute([$app['employee_id'], $msg, $type]);

    echo json_encode(['success' => true, 'message' => 'Application ' . $status]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to process application']);
}
?>
