<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $raw_role = $_POST['role'] ?? 'user';

    $allowed_roles = ['user', 'worker'];
    if (!in_array($raw_role, $allowed_roles, true)) {
        $raw_role = 'user';
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

        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->execute([$raw_role]);
        $role = $stmt->fetch();

        if ($role) {
            $role_id = $role['id'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role_id]);
                $newUserId = (int)$pdo->lastInsertId();

                if ($raw_role === 'worker') {
                    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                    $location = trim($_POST['location'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $skills = trim($_POST['skills'] ?? '');

                    $stmtWk = $pdo->prepare("
                        INSERT INTO worker_profiles (user_id, full_name, category_id, location, phone, skills, availability)
                        VALUES (?, ?, ?, ?, ?, ?, 'available')
                    ");
                    $stmtWk->execute([$newUserId, $username, $category_id, $location, $phone, $skills]);
                }

                $_SESSION['success'] = "Registration successful! Please sign in with your credentials.";
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['error'] = "Registration failed. Username or email may already exist.";
            }
        } else {
            $_SESSION['error'] = "Invalid role configuration.";
        }
    } else {
        $_SESSION['error'] = "All required fields must be filled.";
    }
}
header("Location: login.php");
exit();
?>
