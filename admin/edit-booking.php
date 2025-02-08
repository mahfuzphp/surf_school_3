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

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    $_SESSION['error_message'] = "Invalid booking ID";
    header("Location: /admin/manage-bookings.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $booking_date = trim($_POST['booking_date']);
        $booking_time = trim($_POST['booking_time']);
        $status = trim($_POST['status']);
        
        $errors = [];

        // Validate inputs
        if (empty($booking_date)) {
            $errors[] = "Booking date is required";
        }
        if (empty($booking_time)) {
            $errors[] = "Booking time is required";
        }

        // Check if the time slot is available (excluding current booking)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bookings 
            WHERE lesson_id = ? 
            AND booking_date = ? 
            AND booking_time = ?
            AND id != ?
        ");
        $stmt->execute([$booking['lesson_id'], $booking_date, $booking_time, $booking_id]);
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "This time slot is already booked";
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET booking_date = ?,
                    booking_time = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $booking_date,
                $booking_time,
                $status,
                $booking_id
            ]);

            $pdo->commit();
            $_SESSION['success_message'] = "Booking updated successfully!";
            header("Location: /admin/manage-bookings.php");
            exit();
        } else {
            $_SESSION['error_message'] = $errors;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating booking: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating booking. Please try again.";
    }
}

// Get booking data
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.username as student_name,
           l.title as lesson_title,
           i.username as instructor_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN lessons l ON b.lesson_id = l.id
    JOIN users i ON l.instructor_id = i.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error_message'] = "Booking not found";
    header("Location: /admin/manage-bookings.php");
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
                    <h4 class="mb-0">Edit Booking</h4>
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

                    <div class="mb-4">
                        <h5>Booking Details</h5>
                        <p class="mb-1"><strong>Student:</strong> <?php echo htmlspecialchars($booking['student_name']); ?></p>
                        <p class="mb-1"><strong>Lesson:</strong> <?php echo htmlspecialchars($booking['lesson_title']); ?></p>
                        <p class="mb-1"><strong>Instructor:</strong> <?php echo htmlspecialchars($booking['instructor_name']); ?></p>
                    </div>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Booking Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date"
                                value="<?php echo htmlspecialchars($booking['booking_date']); ?>"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="booking_time" class="form-label">Booking Time</label>
                            <select class="form-select" id="booking_time" name="booking_time" required>
                                <?php
                                $start = 9; // 9 AM
                                $end = 17; // 5 PM
                                for ($i = $start; $i <= $end; $i++) {
                                    $time = sprintf("%02d:00:00", $i);
                                    $display_time = date("g:i A", strtotime($time));
                                    $selected = ($booking['booking_time'] == $time) ? 'selected' : '';
                                    echo "<option value=\"$time\" $selected>$display_time</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Booking</button>
                            <a href="/admin/manage-bookings.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 