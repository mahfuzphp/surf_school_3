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

// Handle booking deletion
if (isset($_POST['delete_booking'])) {
    try {
        $booking_id = (int)$_POST['booking_id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);

        $pdo->commit();
        $_SESSION['success_message'] = "Booking deleted successfully";
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting booking: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting booking. Please try again.";
    }

    header("Location: /admin/manage-bookings.php");
    exit();
}

// Get all bookings with related information
$stmt = $pdo->query("
    SELECT b.*, 
           u.username as student_name,
           l.title as lesson_title,
           l.price as lesson_price,
           i.username as instructor_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN lessons l ON b.lesson_id = l.id
    JOIN users i ON l.instructor_id = i.id
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Bookings</h2>
        <a href="add-booking.php" class="btn btn-primary">Add New Booking</a>
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

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Lesson</th>
                            <th>Instructor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['lesson_title']); ?></td>
                                <td><?php echo htmlspecialchars($booking['instructor_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                                            echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger');
                                                            ?>">
                                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-booking.php?id=<?php echo $booking['id']; ?>"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="delete_booking"
                                                class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
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