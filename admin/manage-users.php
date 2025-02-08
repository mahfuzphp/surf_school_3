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

// Handle user deletion (soft delete)
if (isset($_POST['delete_user'])) {
    try {
        $user_id = (int)$_POST['user_id'];

        // Start transaction
        $pdo->beginTransaction();

        // Delete related bookings first
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$user_id, $_SESSION['user_id']]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "User deleted successfully";
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
    }

    header("Location: /admin/manage-users.php");
    exit();
}

// Get all active users
$stmt = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <a href="add-user.php" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Add New User
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if ($user['profile_image']): ?>
                                        <img src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                            class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'danger' : ($user['user_type'] === 'instructor' ? 'success' : 'primary'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['user_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form action="" method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user"
                                                    class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>