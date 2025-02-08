<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

include '../config/database.php';

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?"); // Prevent admin self-deletion
    $stmt->execute([$user_id, $_SESSION['user_id']]);
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <a href="add-user.php" class="btn btn-primary">Add New User</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
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