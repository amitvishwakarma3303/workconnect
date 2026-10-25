<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_name = $_POST['role'] ?? 'employee';

    // Never trust role from client: only allow public self-registration roles.
    $allowed_roles = ['employee', 'employer'];
    if (!in_array($role_name, $allowed_roles, true)) {
        $role_name = 'employee';
    }

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            header("Location: login.php");
            exit();
        }
        if (!preg_match('/^[a-zA-Z0-9 _.-]{3,50}$/', $username)) {
            $_SESSION['error'] = "Username must be 3-50 characters and use letters, numbers, space, dot, underscore or hyphen.";
            header("Location: login.php");
            exit();
        }

        // Get role ID
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->execute([$role_name]);
        $role = $stmt->fetch();
        
        if ($role) {
            $role_id = $role['id'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role_id]);
                
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                // Do not leak SQL/internal details to users.
                $_SESSION['error'] = "Registration failed. Username or email may already exist.";
            }
        } else {
            $_SESSION['error'] = "Invalid role selected.";
        }
    } else {
        $_SESSION['error'] = "All fields are required.";
    }
}
header("Location: login.php");
exit();
?>
