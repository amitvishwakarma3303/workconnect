<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_name'];

            // Route based on role
            switch ($user['role_name']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'employer':
                    header("Location: employer/dashboard.php");
                    break;
                case 'employee':
                    header("Location: employee/dashboard.php");
                    break;
                default:
                    $_SESSION['error'] = "Invalid user role.";
                    header("Location: login.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password.";
        }
    } else {
        $_SESSION['error'] = "All fields are required.";
    }
}
header("Location: login.php");
exit();
?>
