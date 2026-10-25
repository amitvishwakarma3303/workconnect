<?php
// includes/apply_job.php
require_once 'db_connect.php';
require_once 'csrf.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    echo json_encode(['success' => false, 'error' => 'Not authenticated as employee']);
    exit();
}
if (!csrf_validate(csrf_token_from_request() ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid request token']);
    exit();
}

$employee_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$job_id = isset($data['job_id']) && is_numeric($data['job_id']) ? (int) $data['job_id'] : null;

if (!$job_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid job ID']);
    exit();
}

try {
    // Ensure job exists.
    $job_exists = $pdo->prepare("SELECT id FROM jobs WHERE id = ?");
    $job_exists->execute([$job_id]);
    if (!$job_exists->fetchColumn()) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit();
    }

    // Check if already applied
    $check = $pdo->prepare("SELECT id FROM job_applications WHERE job_id = ? AND employee_id = ?");
    $check->execute([$job_id, $employee_id]);
    
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'error' => 'You have already applied for this job']);
        exit();
    }

    // Apply
    $stmt = $pdo->prepare("INSERT INTO job_applications (job_id, employee_id) VALUES (?, ?)");
    $stmt->execute([$job_id, $employee_id]);

    // Notify Employer
    $stmt_job = $pdo->prepare("SELECT employer_id, title FROM jobs WHERE id = ?");
    $stmt_job->execute([$job_id]);
    $job = $stmt_job->fetch();
    
    if ($job) {
        $msg = "New application received from " . $_SESSION['username'] . " for your job posting: " . $job['title'];
        $stmt_notify = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'application')");
        $stmt_notify->execute([$job['employer_id'], $msg]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to submit application']);
}
?>
