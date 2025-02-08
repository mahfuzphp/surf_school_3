<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

checkLogin();

// Check if user is instructor
if ($_SESSION['user_type'] !== 'instructor') {
    header('Location: ../index.php');
    exit();
}

include '../config/database.php';

// Get all lessons with booking counts
$stmt = $pdo->query("
    SELECT l.*, 
           COUNT(DISTINCT b.id) as total_bookings,
           COUNT(DISTINCT b.user_id) as unique_students
    FROM lessons l
    LEFT JOIN bookings b ON l.id = b.lesson_id
    GROUP BY l.id
    ORDER BY l.created_at DESC
");
$lessons = $stmt->fetchAll();

// Handle lesson status toggle
if (isset($_POST['toggle_status'])) {
    $lesson_id = (int)$_POST['lesson_id'];
    $new_status = $_POST['new_status'] === 'active' ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE lessons SET is_active = ? WHERE id = ?");
    $stmt->execute([$new_status, $lesson_id]);

    $_SESSION['success'] = "Lesson status updated successfully";
    header('Location: my-lessons.php');
    exit();
}
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Lessons</h2>
        <a href="add-lesson.php" class="btn btn-primary">Create New Lesson</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($lessons as $lesson): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                            <span class="badge bg-<?php echo $lesson['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $lesson['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>

                        <p class="card-text"><?php echo htmlspecialchars($lesson['description']); ?></p>

                        <div class="row g-2 mb-3">
                            <div class="col-auto">
                                <span class="badge bg-primary">
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo $lesson['unique_students']; ?> Students
                                </span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-info">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <?php echo $lesson['total_bookings']; ?> Bookings
                                </span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-success">
                                    <i class="fas fa-tag me-1"></i>
                                    $<?php echo number_format($lesson['price'], 2); ?>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="edit-lesson.php?id=<?php echo $lesson['id']; ?>"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="view-bookings.php?lesson_id=<?php echo $lesson['id']; ?>"
                                    class="btn btn-sm btn-outline-info">View Bookings</a>
                            </div>

                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                <input type="hidden" name="new_status"
                                    value="<?php echo $lesson['is_active'] ? 'inactive' : 'active'; ?>">
                                <button type="submit" name="toggle_status"
                                    class="btn btn-sm btn-outline-<?php echo $lesson['is_active'] ? 'warning' : 'success'; ?>">
                                    <?php echo $lesson['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Created: <?php echo date('M j, Y', strtotime($lesson['created_at'])); ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($lessons)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">No Lessons Created Yet</h3>
            <p>Start by creating your first lesson</p>
            <a href="add-lesson.php" class="btn btn-primary">Create Lesson</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>