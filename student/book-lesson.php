<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

checkLogin();

// Get lesson details
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
include '../config/database.php';

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('Location: ../lessons.php');
    exit();
}
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
            <p class="lead"><?php echo htmlspecialchars($lesson['description']); ?></p>
            <p><strong>Price: $<?php echo htmlspecialchars($lesson['price']); ?></strong></p>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Book This Lesson</h3>
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