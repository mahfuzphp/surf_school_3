<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check login and user type
checkLogin();

if ($_SESSION['user_type'] !== 'instructor') {
    $_SESSION['error_message'] = "Access denied. Instructor privileges required.";
    header("Location: /login.php");
    exit();
}

// Get lesson ID from URL
$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;

// Verify lesson exists and belongs to instructor
$stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE id = ? AND instructor_id = ?
");
$stmt->execute([$lesson_id, $_SESSION['user_id']]);
$lesson = $stmt->fetch();

if (!$lesson) {
    $_SESSION['error_message'] = "Lesson not found or access denied.";
    header("Location: /instructor/my-lessons.php");
    exit();
}

// Get all bookings for this lesson
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.username, 
           u.email,
           u.profile_image,
           u.profile_description
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.lesson_id = ?
    ORDER BY b.booking_date ASC, b.booking_time ASC
");
$stmt->execute([$lesson_id]);
$bookings = $stmt->fetchAll();

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $booking_id = (int)$_POST['booking_id'];
        $new_status = $_POST['status'];
        
        if (!in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
            throw new Exception("Invalid status");
        }
        
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = ? 
            WHERE id = ? AND lesson_id = ?
        ");
        $stmt->execute([$new_status, $booking_id, $lesson_id]);
        
        $_SESSION['success_message'] = "Booking status updated successfully";
        header("Location: /instructor/view-bookings.php?lesson_id=" . $lesson_id);
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating booking status";
        header("Location: /instructor/view-bookings.php?lesson_id=" . $lesson_id);
        exit();
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($lesson['title']); ?></h2>
                    <p class="text-muted mb-0">Manage Bookings</p>
                </div>
                <a href="/instructor/my-lessons.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Lessons
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

            <?php if (empty($bookings)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>No Bookings Found</h5>
                        <p class="text-muted">There are currently no bookings for this lesson.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($booking['profile_image']): ?>
                                                        <img src="/uploads/profile_images/<?php echo htmlspecialchars($booking['profile_image']); ?>"
                                                            class="rounded-circle me-2"
                                                            style="width: 32px; height: 32px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div><?php echo htmlspecialchars($booking['username']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $booking['status'] === 'confirmed' ? 'success' : 
                                                        ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto me-2">
                                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                                        Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 