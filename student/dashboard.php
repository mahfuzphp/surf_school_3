<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';
include '../includes/get_weather.php';

checkLogin();

include '../config/database.php';

// Get user's details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get upcoming bookings
$stmt = $pdo->prepare("
    SELECT b.*, l.title as lesson_title, l.description, l.price
    FROM bookings b 
    JOIN lessons l ON b.lesson_id = l.id 
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
?>

<div class="dashboard-container mt-5 pt-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Welcome Section -->
                <div class="welcome-section mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="welcome-text">
                            <h1 class="display-6 fw-bold text-primary mb-1">Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 🏄‍♂️</h1>
                            <p class="text-muted lead">Ready for your next surfing adventure?</p>
                        </div>
                        <div class="welcome-actions">
                            <a href="../lessons.php" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-plus me-2"></i>Book New Lesson
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Weather Widget -->
                <?php if ($weather): ?>
                    <?php echo getWeatherDisplay($weather); ?>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value">$<?php echo number_format($total_spent ?? 0, 2); ?></h3>
                                    <p class="stat-label">Total Investment</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value"><?php echo count($upcoming_bookings); ?></h3>
                                    <p class="stat-label">Upcoming Lessons</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></h3>
                                    <p class="stat-label">Member Since</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="row g-4">
                    <!-- Profile Section -->
                    <div class="col-lg-4">
                        <div class="profile-card">
                            <div class="profile-cover"></div>
                            <div class="profile-content">
                                <div class="profile-image">
                                    <?php if ($user['profile_image']): ?>
                                        <img src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>"
                                            alt="Profile Image">
                                    <?php else: ?>
                                        <div class="default-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-info">
                                    <h4 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                                    <a href="profile.php" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="fas fa-edit me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Lessons Section -->
                    <div class="col-lg-8">
                        <div class="lessons-card">
                            <div class="card-header">
                                <h5 class="mb-0">Upcoming Lessons</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($upcoming_bookings)): ?>
                                    <div class="empty-state">
                                        <img src="../assets/images/no-data.svg" alt="No bookings">
                                        <h6>No upcoming lessons scheduled</h6>
                                        <a href="../lessons.php" class="btn btn-primary btn-sm rounded-pill mt-3">Browse Lessons</a>
                                    </div>
                                <?php else: ?>
                                    <div class="lessons-list">
                                        <?php foreach ($upcoming_bookings as $booking): ?>
                                            <div class="lesson-item">
                                                <div class="lesson-icon">
                                                    <i class="fas fa-surfing"></i>
                                                </div>
                                                <div class="lesson-details">
                                                    <h6><?php echo htmlspecialchars($booking['lesson_title']); ?></h6>
                                                    <div class="lesson-meta">
                                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                                                        <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></span>
                                                        <span class="price-badge">$<?php echo htmlspecialchars($booking['price']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center mt-4">
                                        <a href="my-bookings.php" class="btn btn-outline-primary rounded-pill">
                                            View All Bookings
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Container */
    .dashboard-container {
        background-color: #f8f9fa;
        min-height: calc(100vh - 60px);
        padding-bottom: 2rem;
    }

    /* Welcome Section */
    .welcome-section {
        padding: 2rem 0;
    }

    .welcome-text h1 {
        color: #2c3e50;
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-card-inner {
        padding: 1.5rem;
        display: flex;
        align-items: center;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    .stat-details {
        flex-grow: 1;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }

    .stat-label {
        color: #64748b;
        margin: 0;
        font-size: 0.875rem;
    }

    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .profile-cover {
        height: 80px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }

    .profile-content {
        padding: 0 1.5rem 1.5rem;
        text-align: center;
        position: relative;
    }

    .profile-image {
        width: 100px;
        height: 100px;
        margin: -50px auto 1rem;
        position: relative;
    }

    .profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .default-avatar {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        border: 4px solid white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Lessons Card */
    .lessons-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .lessons-card .card-header {
        background: white;
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .lessons-list {
        padding: 0.5rem;
    }

    .lesson-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        transition: background-color 0.3s ease;
    }

    .lesson-item:hover {
        background-color: #f8f9fa;
    }

    .lesson-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .lesson-details {
        flex-grow: 1;
    }

    .lesson-meta {
        display: flex;
        gap: 1rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .lesson-meta span {
        display: flex;
        align-items: center;
    }

    .lesson-meta i {
        margin-right: 0.5rem;
    }

    .price-badge {
        background: #e0e7ff;
        color: #6366f1;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state img {
        width: 150px;
        margin-bottom: 1rem;
    }

    .empty-state h6 {
        color: #64748b;
        margin-bottom: 1rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .welcome-section {
            text-align: center;
        }

        .welcome-actions {
            margin-top: 1rem;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .lesson-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>