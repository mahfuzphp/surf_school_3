<?php
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

// Handle lesson deletion before any output
if (isset($_POST['delete_lesson'])) {
    try {
        $lesson_id = (int)$_POST['lesson_id'];

        // Start transaction
        $pdo->beginTransaction();

        // Check if lesson has any bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE lesson_id = ?");
        $stmt->execute([$lesson_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Cannot delete lesson with existing bookings");
        }

        // Delete the lesson
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ? AND instructor_id = ?");
        $stmt->execute([$lesson_id, $_SESSION['user_id']]);

        $pdo->commit();
        $_SESSION['success_message'] = "Lesson deleted successfully";
        header("Location: /instructor/my-lessons.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: /instructor/my-lessons.php");
        exit();
    }
}

// Get instructor's lessons
$stmt = $pdo->prepare("
    SELECT l.*, 
           COUNT(b.id) as booking_count,
           GROUP_CONCAT(DISTINCT b.booking_date ORDER BY b.booking_date ASC) as upcoming_dates
    FROM lessons l
    LEFT JOIN bookings b ON l.id = b.lesson_id AND b.booking_date >= CURDATE()
    WHERE l.instructor_id = ?
    GROUP BY l.id
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$lessons = $stmt->fetchAll();

// Only include header/navbar AFTER all potential redirects
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Lessons</h2>
                <a href="add-lesson.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Lesson
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

            <?php if (empty($lessons)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5>No Lessons Found</h5>
                        <p class="text-muted">You haven't created any lessons yet.</p>
                        <a href="add-lesson.php" class="btn btn-primary">Create Your First Lesson</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">

                                <?php if ($lesson['lesson_image']): ?>
                                    <img src="/uploads/lesson_images/<?php echo htmlspecialchars($lesson['lesson_image']); ?>"
                                        class="card-img-top"
                                        style="height: 200px; object-fit: cover;"
                                        alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo substr(htmlspecialchars($lesson['description']), 0, 100) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">$<?php echo number_format($lesson['price'], 2); ?></span>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($lesson['level']); ?></span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="view-bookings.php?lesson_id=<?php echo $lesson['id']; ?>"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Bookings (<?php echo $lesson['booking_count']; ?>)
                                        </a>
                                        <div class="btn-group">
                                            <a href="edit-lesson.php?id=<?php echo $lesson['id']; ?>"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="" method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                                <button type="submit" name="delete_lesson"
                                                    class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>