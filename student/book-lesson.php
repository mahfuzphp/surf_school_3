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

// Get lesson details
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$lesson_id) {
    $_SESSION['error_message'] = "Invalid lesson ID";
    header("Location: /lessons.php");
    exit();
}

$stmt = $pdo->prepare("SELECT l.*, u.username as instructor_name FROM lessons l JOIN users u ON l.instructor_id = u.id WHERE l.id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('Location: ../lessons.php');
    exit();
}

// Get surf conditions for the next 7 days
$stmt = $pdo->prepare("
    SELECT * FROM surf_conditions 
    WHERE date_time >= CURDATE() 
    AND date_time <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY date_time ASC
");
$stmt->execute();
$forecast = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$conditions = null;
$isSuitable = false;
$warningMessage = '';

foreach ($forecast as $day) {
    if (date('Y-m-d', strtotime($day['date_time'])) === $selectedDate) {
        $conditions = $day;
        $isSuitable = $conditions['beginner_status'] === 'good' || $conditions['beginner_status'] === 'fair';

        if (!$isSuitable) {
            $warningMessage = "Warning: Surf conditions are rated as '" .
                ($conditions['beginner_status']) .
                "' for beginners on this date. Consider choosing another date.";
        }
        break;
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="booking-container">
    <?php if ($warningMessage): ?>
        <div class="warning-banner">
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $warningMessage; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-5">
        <!-- Lesson Header -->
        <div class="lesson-header text-center mb-5">
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($lesson['title']); ?></h1>
            <p class="lead text-muted"><?php echo htmlspecialchars($lesson['description']); ?></p>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Lesson Info Card -->
            <div class="col-lg-6">
                <div class="card rounded-lg shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-4">
                        <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i>Lesson Details</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                                <h5>Duration</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($lesson['duration']); ?> minutes</p>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-dollar-sign fa-2x text-success mb-3"></i>
                                <h5>Price</h5>
                                <p class="mb-0">$<?php echo htmlspecialchars($lesson['price']); ?></p>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user-tie fa-2x text-info mb-3"></i>
                                <h5>Instructor</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($lesson['instructor_name']); ?></p>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users fa-2x text-warning mb-3"></i>
                                <h5>Group Size</h5>
                                <p class="mb-0">Max <?php echo htmlspecialchars($lesson['max_students']); ?></p>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <span class="badge bg-primary px-4 py-2 fs-6">Level: <?php echo htmlspecialchars($lesson['level']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Card -->
            <div class="col-lg-6">
                <div class="card rounded-lg shadow-sm h-100">
                    <div class="card-header bg-success text-white p-4">
                        <h3 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Book Your Session</h3>
                    </div>
                    <div class="card-body p-4">
                        <form action="process-booking.php" method="POST" id="bookingForm">
                            <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">

                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Date</label>
                                <select class="form-select form-select-lg" name="booking_date" id="booking_date" required>
                                    <?php foreach ($forecast as $day):
                                        $date = date('Y-m-d', strtotime($day['date_time']));
                                        $status = $day['beginner_status'];
                                        $isPoorOrDangerous = ($status === 'poor' || $status === 'dangerous');
                                    ?>
                                        <option value="<?php echo $date; ?>"
                                            <?php echo $date === $selectedDate ? 'selected' : ''; ?>
                                            <?php echo $isPoorOrDangerous ? 'disabled class="text-danger"' : ''; ?>>
                                            <?php echo date('D, M j', strtotime($day['date_time'])); ?>
                                            (<?php echo ucfirst($status); ?>)
                                            <?php if ($isPoorOrDangerous): ?>
                                                - Not Available
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Time</label>
                                <select class="form-select form-select-lg" name="booking_time" required>
                                    <option value="">Choose time...</option>
                                    <?php
                                    $times = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];
                                    foreach ($times as $time): ?>
                                        <option value="<?php echo $time; ?>">
                                            <?php echo date('g:i A', strtotime($time)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check-circle me-2"></i>Confirm Booking
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Weather Conditions Card -->
            <?php if ($conditions): ?>
                <div class="col-12">
                    <div class="card rounded-lg shadow-sm">
                        <div class="card-header bg-info text-white p-4">
                            <h3 class="mb-0"><i class="fas fa-water me-2"></i>Surf Conditions</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <h5 class="text-muted mb-3">Wave Height</h5>
                                    <h2 class="mb-0"><?php echo number_format($conditions['wave_height'], 1); ?>m</h2>
                                </div>
                                <div class="col-md-4 text-center border-start border-end">
                                    <h5 class="text-muted mb-3">Wind Speed</h5>
                                    <h2 class="mb-0"><?php echo $conditions['wind_speed']; ?> km/h</h2>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h5 class="text-muted mb-3">Temperature</h5>
                                    <h2 class="mb-0"><?php echo $conditions['temperature']; ?>°C</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .booking-container {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        min-height: 100vh;
    }

    .warning-banner {
        background: rgba(255, 193, 7, 0.1);
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .card {
        border: none;
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .info-item {
        text-align: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: #e9ecef;
        transform: translateY(-3px);
    }

    .form-select {
        border: 2px solid #dee2e6;
        padding: 0.75rem;
        border-radius: 10px;
    }

    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>