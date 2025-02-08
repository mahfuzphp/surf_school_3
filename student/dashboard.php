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

// Get user's details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get upcoming bookings
$stmt = $pdo->prepare("
    SELECT b.*, 
           l.title as lesson_title, 
           l.description, 
           l.price,
           i.username as instructor_name
    FROM bookings b 
    JOIN lessons l ON b.lesson_id = l.id 
    JOIN users i ON l.instructor_id = i.id
    WHERE b.user_id = ? AND b.booking_date >= CURDATE()
    ORDER BY b.booking_date ASC, b.booking_time ASC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$upcoming_bookings = $stmt->fetchAll();

// Get total spent on lessons
$stmt = $pdo->prepare("
    SELECT SUM(l.price) as total_spent
    FROM bookings b 
    JOIN lessons l ON b.lesson_id = l.id 
    WHERE b.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetchColumn();

// Get weather data
$weather = fetchWeatherData();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="h3 mb-0">Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 🏄‍♂️</h1>
                            <p class="text-muted">Ready for your next surfing adventure?</p>
                        </div>
                        <a href="/lessons.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Book New Lesson
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather Widget -->
    <?php if ($weather): ?>
        <div class="row mb-4">
            <div class="col-12">
                <?php echo getWeatherDisplay($weather); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-primary text-white">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Total Investment</h6>
                            <h2 class="card-title mb-0">$<?php echo number_format($total_spent ?? 0, 2); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-success text-white">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Upcoming Lessons</h6>
                            <h2 class="card-title mb-0"><?php echo count($upcoming_bookings); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-info text-white">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Member Since</h6>
                            <h2 class="card-title mb-0"><?php echo date('M Y', strtotime($user['created_at'])); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($user['profile_image']): ?>
                            <img src="/uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                class="rounded-circle mb-3"
                                style="width: 100px; height: 100px; object-fit: cover;"
                                alt="Profile Image">
                        <?php else: ?>
                            <div class="default-avatar mb-3">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Lessons -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Upcoming Lessons</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcoming_bookings)): ?>
                        <div class="text-center py-4">
                            <h6 class="text-muted">No upcoming lessons scheduled</h6>
                            <a href="/lessons.php" class="btn btn-primary mt-3">Browse Lessons</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_bookings as $booking): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($booking['lesson_title']); ?></h6>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?>
                                                <i class="fas fa-clock ms-3 me-2"></i>
                                                <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                                            </p>
                                        </div>
                                        <div class="ms-3">
                                            <span class="badge bg-primary">$<?php echo htmlspecialchars($booking['price']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="my-bookings.php" class="btn btn-outline-primary">View All Bookings</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .default-avatar {
        width: 100px;
        height: 100px;
        background-color: var(--bs-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto;
    }
</style>

<?php include '../includes/footer.php'; ?>