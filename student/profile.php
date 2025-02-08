<?php
// Start the session and include required files at the very top
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check login before any output
checkLogin();

// Check if user is a student
if ($_SESSION['user_type'] !== 'student') {
    $_SESSION['error_message'] = "Access denied. Student privileges required.";
    header("Location: /login.php");
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found";
    header("Location: /login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        $profile_description = trim($_POST['profile_description']);

        $errors = [];

        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if email is already taken by another user
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
        $profile_image = null;
        if (!empty($_FILES['profile_image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG, PNG and WEBP are allowed";
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $errors[] = "File size too large. Maximum size is 5MB";
            } else {
                $upload_dir = "../uploads/profile_images/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $profile_image = uniqid() . '.' . $file_extension;

                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $profile_image)) {
                    $errors[] = "Failed to upload image";
                }
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // Build update query
            $sql = "UPDATE users SET email = ?, profile_description = ?";
            $params = [$email, $profile_description];

            // Add password to update if changed
            if (!empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            // Add profile image if uploaded
            if ($profile_image) {
                $sql .= ", profile_image = ?";
                $params[] = $profile_image;

                // Delete old profile image
                if ($user['profile_image'] && file_exists("../uploads/profile_images/" . $user['profile_image'])) {
                    unlink("../uploads/profile_images/" . $user['profile_image']);
                }
            }

            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pdo->commit();
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: /student/profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = $errors;
            // Delete uploaded image if there were errors
            if ($profile_image && file_exists("../uploads/profile_images/" . $profile_image)) {
                unlink("../uploads/profile_images/" . $profile_image);
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating profile: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating profile. Please try again.";
    }
}

// After all potential redirects, include the header and navbar
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Profile</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            if (is_array($_SESSION['error_message'])) {
                                echo '<ul class="mb-0">';
                                foreach ($_SESSION['error_message'] as $error) {
                                    echo '<li>' . htmlspecialchars($error) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo htmlspecialchars($_SESSION['error_message']);
                            }
                            unset($_SESSION['error_message']);
                            ?>
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
                                <img src="/uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                    class="rounded-circle mb-3"
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                    alt="Profile Image">
                            <?php else: ?>
                                <div class="default-avatar mb-3">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Change Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, WEBP</small>
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
                            <label for="profile_description" class="form-label">Profile Description</label>
                            <textarea class="form-control" id="profile_description" name="profile_description" rows="4"><?php
                                                                                                                        echo htmlspecialchars($user['profile_description'] ?? '');
                                                                                                                        ?></textarea>
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="/student/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .default-avatar {
        width: 150px;
        height: 150px;
        background-color: var(--bs-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        margin: 0 auto;
    }
</style>

<?php include '../includes/footer.php'; ?>