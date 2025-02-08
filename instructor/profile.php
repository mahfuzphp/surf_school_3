<?php
// Start session and include files before ANY output
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check login and user type immediately
checkLogin();

if ($_SESSION['user_type'] !== 'instructor') {
    $_SESSION['error_message'] = "Access denied. Instructor privileges required.";
    header("Location: /login.php");
    exit();
}

// Get user data early
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found";
    header("Location: /login.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $profile_description = trim($_POST['profile_description']);
        $errors = [];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if email is taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Email is already taken";
        }

        // Handle password change if requested
        if (!empty($current_password)) {
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            } elseif (empty($new_password)) {
                $errors[] = "New password is required";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match";
            }
        }

        // Handle profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG, PNG and GIF are allowed";
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $errors[] = "File size too large. Maximum size is 5MB";
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // Update profile
            $sql = "UPDATE users SET email = ?, profile_description = ?";
            $params = [$email, $profile_description];

            // Add password to update if changed
            if (!empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            // Handle profile image
            if (!empty($_FILES['profile_image']['name'])) {
                $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = "../uploads/profile_images/" . $new_filename;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $sql .= ", profile_image = ?";
                    $params[] = $new_filename;

                    // Delete old profile image if exists
                    if ($user['profile_image']) {
                        @unlink("../uploads/profile_images/" . $user['profile_image']);
                    }
                }
            }

            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $pdo->commit();
                $_SESSION['success_message'] = "Profile updated successfully!";
                header("Location: /instructor/profile.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = $errors;
            header("Location: /instructor/profile.php");
            exit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating profile: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating profile. Please try again.";
        header("Location: /instructor/profile.php");
        exit();
    }
}

// Only include header/navbar AFTER all potential redirects
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Edit Instructor Profile</h2>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <?php if ($user['profile_image']): ?>
                                <img src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                    class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Change Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="profile_description" class="form-label">Professional Bio</label>
                            <textarea class="form-control" id="profile_description" name="profile_description"
                                rows="4" placeholder="Share your experience and teaching philosophy"><?php echo htmlspecialchars($user['profile_description'] ?? ''); ?></textarea>
                        </div>

                        <hr class="my-4">

                        <h5>Change Password</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>