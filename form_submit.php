<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.html");
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$city = trim($_POST['city'] ?? '');
$message = trim($_POST['message'] ?? '');
$source_page = ($_POST['source_page'] ?? 'contact') === 'home' ? 'home' : 'contact';

if ($name === '' || $email === '' || $message === '') {
    header("Location: " . ($source_page === 'home' ? 'home.html' : 'contact.html') . "?form=error");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . ($source_page === 'home' ? 'home.html' : 'contact.html') . "?form=invalid_email");
    exit();
}

if (strlen($name) > 150 || strlen($email) > 190 || strlen($city) > 150 || strlen($message) > 4000) {
    header("Location: " . ($source_page === 'home' ? 'home.html' : 'contact.html') . "?form=too_long");
    exit();
}

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
if (is_string($user_agent) && strlen($user_agent) > 255) {
    $user_agent = substr($user_agent, 0, 255);
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO contact_forms (name, email, city, message, source_page, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $city, $message, $source_page, $ip, $user_agent]);

    header("Location: " . ($source_page === 'home' ? 'home.html' : 'contact.html') . "?form=success");
    exit();
} catch (PDOException $e) {
    header("Location: " . ($source_page === 'home' ? 'home.html' : 'contact.html') . "?form=error");
    exit();
}
?>
