<?php
session_start();
include_once 'config/database.php';
include_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: login.php');
        exit();
    }

    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify credentials
    if ($user && password_verify($password, $user['password'])) {

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];

        // Redirect based on user type
        switch ($user['user_type']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'instructor':
                header('Location: instructor/dashboard.php');
                break;
            case 'student':
                header('Location: student/dashboard.php');
                break;
            default:
                header('Location: index.php');
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: login.php');
        exit();
    }
} else {
    // If someone tries to access this file directly
    header('Location: login.php');
    exit();
}
