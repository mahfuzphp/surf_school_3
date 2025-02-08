<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Get and sanitize form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

$errors = [];

// Validate username
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
    $errors[] = "Username can only contain letters, numbers, underscores, and hyphens";
} else {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username already taken";
    }
}

// Validate email
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
} else {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already registered";
    }
}

// Validate password
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long";
} elseif (!preg_match("/[A-Z]/", $password)) {
    $errors[] = "Password must contain at least one uppercase letter";
} elseif (!preg_match("/[a-z]/", $password)) {
    $errors[] = "Password must contain at least one lowercase letter";
} elseif (!preg_match("/[0-9]/", $password)) {
    $errors[] = "Password must contain at least one number";
}

// Validate password confirmation
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Handle profile image upload
$profile_image = null;
if (!empty($_FILES['profile_image']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
        $errors[] = "Invalid file type. Only JPG, PNG and WEBP are allowed";
    } elseif ($_FILES['profile_image']['size'] > $max_size) {
        $errors[] = "File size too large. Maximum size is 5MB";
    } else {
        $upload_dir = "uploads/";
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $profile_image = uniqid() . '.' . $file_extension;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $profile_image)) {
            $errors[] = "Failed to upload profile image";
        }
    }
}

if (empty($errors)) {
    try {
        // Create upload directory if it doesn't exist
        if (!empty($_FILES['profile_image']['name'])) {
            $upload_dir = "uploads/profile_images/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (
                username, 
                email, 
                password, 
                user_type,
                profile_image,
                profile_description,
                created_at
            ) VALUES (?, ?, ?, 'student', ?, ?, NOW())
        ");

        $stmt->execute([
            $username,
            $email,
            $hashed_password,
            $profile_image,
            $_POST['profile_description'] ?? null
        ]);

        // Get the new user's ID
        $user_id = $pdo->lastInsertId();

        // Commit transaction
        $pdo->commit();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = 'student';

        // Redirect to student dashboard with success message
        $_SESSION['success_message'] = "Registration successful! Welcome to Surf School.";
        header("Location: /student/dashboard.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();

        // Delete uploaded image if database insert fails
        if ($profile_image && file_exists($upload_dir . $profile_image)) {
            unlink($upload_dir . $profile_image);
        }

        error_log("Registration error: " . $e->getMessage());
        $_SESSION['error_message'] = "Registration failed. Please try again.";
        header("Location: /register.php");
        exit();
    }
} else {
    // Store errors and form data in session
    $_SESSION['error_message'] = $errors;
    $_SESSION['form_data'] = [
        'username' => $username,
        'email' => $email,
        'profile_description' => $_POST['profile_description'] ?? ''
    ];

    header("Location: /register.php");
    exit();
}
