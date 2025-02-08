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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = (int)$_POST['user_id'];
        $lesson_id = (int)$_POST['lesson_id'];
        $booking_date = trim($_POST['booking_date']);
        $booking_time = trim($_POST['booking_time']);
        $status = trim($_POST['status']);
        
        $errors = [];

        // Validate inputs
        if ($user_id <= 0) {
            $errors[] = "Please select a student";
        }
        if ($lesson_id <= 0) {
            $errors[] = "Please select a lesson";
        }
        if (empty($booking_date)) {
            $errors[] = "Booking date is required";
        }
        if (empty($booking_time)) {
            $errors[] = "Booking time is required";
        }

        // Check if the time slot is available
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bookings 
            WHERE lesson_id = ? 
            AND booking_date = ? 
            AND booking_time = ?
        ");
        $stmt->execute([$lesson_id, $booking_date, $booking_time]);
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "This time slot is already booked";
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    user_id, 
                    lesson_id, 
                    booking_date, 
                    booking_time,
                    status,
                    created_at
                ) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $user_id,
                $lesson_id,
                $booking_date,
                $booking_time,
                $status
            ]);

            $pdo->commit();
            $_SESSION['success_message'] = "Booking added successfully!";
            header("Location: /admin/manage-bookings.php");
            exit();
        } else {
            $_SESSION['error_message'] = $errors;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding booking: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding booking. Please try again.";
    }
}

// Get all students
$stmt = $pdo->query("SELECT id, username, email FROM users WHERE user_type = 'student'");
$students = $stmt->fetchAll();

// Get all active lessons
$stmt = $pdo->query("
    SELECT l.*, u.username as instructor_name 
    FROM lessons l 
    JOIN users u ON l.instructor_id = u.id 
    WHERE l.is_active = 1
");
$lessons = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Booking</h4>
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

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select Student</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Choose a student...</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>"
                                        <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $student['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['username']); ?>
                                        (<?php echo htmlspecialchars($student['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="lesson_id" class="form-label">Select Lesson</label>
                            <select class="form-select" id="lesson_id" name="lesson_id" required>
                                <option value="">Choose a lesson...</option>
                                <?php foreach ($lessons as $lesson): ?>
                                    <option value="<?php echo $lesson['id']; ?>"
                                        <?php echo (isset($_POST['lesson_id']) && $_POST['lesson_id'] == $lesson['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lesson['title']); ?>
                                        (Instructor: <?php echo htmlspecialchars($lesson['instructor_name']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Booking Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date"
                                value="<?php echo isset($_POST['booking_date']) ? htmlspecialchars($_POST['booking_date']) : ''; ?>"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="booking_time" class="form-label">Booking Time</label>
                            <select class="form-select" id="booking_time" name="booking_time" required>
                                <option value="">Choose a time...</option>
                                <?php
                                $start = 9; // 9 AM
                                $end = 17; // 5 PM
                                for ($i = $start; $i <= $end; $i++) {
                                    $time = sprintf("%02d:00:00", $i);
                                    $display_time = date("g:i A", strtotime($time));
                                    echo "<option value=\"$time\"" . 
                                        (isset($_POST['booking_time']) && $_POST['booking_time'] == $time ? ' selected' : '') . 
                                        ">$display_time</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="confirmed" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="cancelled" <?php echo (isset($_POST['status']) && $_POST['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Booking</button>
                            <a href="/admin/manage-bookings.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 