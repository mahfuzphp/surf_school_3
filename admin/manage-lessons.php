<?php
// Include required files before any output
require_once '../includes/functions.php'; // This will now start the session
require_once '../config/database.php';

// Check login and admin status
checkLogin();

// Check if user is admin
if ($_SESSION['user_type'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: /login.php");
    exit();
}

// Handle lesson deletion
if (isset($_POST['delete_lesson'])) {
    $lesson_id = (int)$_POST['lesson_id'];
    try {
        // Get the lesson image first
        $stmt = $pdo->prepare("SELECT lesson_image FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch();

        // Delete the lesson
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);

        // Delete the image file if it exists
        if ($lesson && $lesson['lesson_image']) {
            $imagePath = '../uploads/lesson_images/' . $lesson['lesson_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $_SESSION['success_message'] = "Lesson deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting lesson: " . $e->getMessage();
    }

    header("Location: manage-lessons.php");
    exit();
}

// Get all lessons with instructor information
try {
    $stmt = $pdo->query("
        SELECT l.*, u.username as instructor_name 
        FROM lessons l 
        LEFT JOIN users u ON l.instructor_id = u.id 
        ORDER BY l.created_at DESC
    ");
    $lessons = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching lessons: " . $e->getMessage();
    $lessons = [];
}

// Now include header and navbar
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Lessons</h2>
        <a href="add-lesson.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Lesson
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Instructor</th>
                            <th>Level</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lessons)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No lessons found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                    <td><?php echo htmlspecialchars($lesson['instructor_name'] ?? 'No instructor'); ?></td>
                                    <td><?php echo htmlspecialchars($lesson['level']); ?></td>
                                    <td>$<?php echo number_format($lesson['price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $lesson['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $lesson['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($lesson['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit-lesson.php?id=<?php echo $lesson['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="" method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                                <button type="submit" name="delete_lesson"
                                                    class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto dismiss alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 3000);
    });
</script>

<?php include '../includes/footer.php'; ?>