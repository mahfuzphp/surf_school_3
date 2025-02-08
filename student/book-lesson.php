<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/get_weather.php';

checkLogin();

// Check if user is a student
if ($_SESSION['user_type'] !== 'student') {
    $_SESSION['error_message'] = "Access denied. Student privileges required.";
    header("Location: /login.php");
    exit();
}

// Get the forecast data
$forecast = fetchMonthlyForecast();

// Get lesson details
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$lesson_id) {
    $_SESSION['error_message'] = "Invalid lesson ID";
    header("Location: /lessons.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('Location: ../lessons.php');
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <!-- Display the forecast widget -->
    <?php echo getMonthlyForecastDisplay($forecast); ?>

    <div class="row justify-content-between">
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
            <p class="lead"><?php echo htmlspecialchars($lesson['description']); ?></p>
            <p><strong>Price: $<?php echo htmlspecialchars($lesson['price']); ?></strong></p>
            <div class="lesson-details mt-4">
                <div class="badge bg-primary me-2">Level: <?php echo htmlspecialchars($lesson['level']); ?></div>
                <div class="badge bg-info me-2">Duration: <?php echo htmlspecialchars($lesson['duration']); ?> mins</div>
                <div class="badge bg-success">Max Students: <?php echo htmlspecialchars($lesson['max_students']); ?></div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Book This Lesson</h3>
                    <form action="process-booking.php" method="POST">
                        <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">

                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Select Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="booking_time" class="form-label">Select Time</label>
                            <select class="form-select" id="booking_time" name="booking_time" required>
                                <option value="">Choose a time...</option>
                                <?php
                                $start = 9; // 9 AM
                                $end = 17; // 5 PM
                                for ($i = $start; $i <= $end; $i++) {
                                    $time = sprintf("%02d:00:00", $i);
                                    echo "<option value=\"$time\">" . date("g:i A", strtotime($time)) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>