<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

checkLogin();

// Check if user is admin
if ($_SESSION['user_type'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: /login.php");
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    $_SESSION['error_message'] = "Invalid user ID";
    header("Location: /admin/manage-users.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $new_password = trim($_POST['new_password']);
        $profile_description = trim($_POST['profile_description']);

        $errors = [];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if email is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email is already taken";
        }

        // Handle profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG, PNG and WEBP are allowed";
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $errors[] = "File size too large. Maximum size is 5MB";
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            // Build update query
            $sql = "UPDATE users SET email = ?, profile_description = ?";
            $params = [$email, $profile_description];

            // Add password to update if provided
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

                    // Delete old profile image
                    if ($user['profile_image']) {
                        @unlink("../uploads/profile_images/" . $user['profile_image']);
                    }
                }
            }

            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pdo->commit();
            $_SESSION['success_message'] = "User updated successfully";
            header("Location: /admin/manage-users.php");
            exit();
        } else {
            $_SESSION['error_message'] = $errors;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating user: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating user. Please try again.";
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "User not found";
    header("Location: /admin/manage-users.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit User</h4>
                </div>
                <div class="card-body">
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
                            <label for="profile_description" class="form-label">Profile Description</label>
                            <textarea class="form-control" id="profile_description" name="profile_description"
                                rows="4"><?php echo htmlspecialchars($user['profile_description'] ?? ''); ?></textarea>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="/admin/manage-users.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>