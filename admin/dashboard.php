<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

checkLogin();

// Check if user is admin
if ($_SESSION['user_type'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: /login.php");
    exit();
}

// Get total users count by type
$stmt = $pdo->query("
    SELECT user_type, COUNT(*) as count 
    FROM users 
    GROUP BY user_type
");
$user_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent bookings
$stmt = $pdo->query("
    SELECT b.*, u.username, l.title as lesson_title, i.username as instructor_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN lessons l ON b.lesson_id = l.id
    JOIN users i ON l.instructor_id = i.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$recent_bookings = $stmt->fetchAll();

// Get revenue stats
$stmt = $pdo->query("
    SELECT 
        SUM(l.price) as total_revenue,
        COUNT(DISTINCT b.id) as total_bookings,
        COUNT(DISTINCT b.user_id) as unique_students
    FROM bookings b
    JOIN lessons l ON b.lesson_id = l.id
");
$stats = $stmt->fetch();

// Get active lessons count
$stmt = $pdo->query("SELECT COUNT(*) FROM lessons WHERE is_active = 1");
$active_lessons = $stmt->fetchColumn();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="admin-dashboard">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 px-0 bg-dark sidebar">
                <div class="sidebar-sticky">
                    <div class="admin-profile text-center py-4">
                        <div class="admin-avatar mb-3">
                            <i class="fas fa-user-shield fa-3x"></i>
                        </div>
                        <h6 class="text-white mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                        <small class="text-muted">Administrator</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-lessons.php">
                                <i class="fas fa-book me-2"></i>
                                Lessons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                Bookings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 main-content">
                <div class="container-fluid py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3">Dashboard Overview</h1>
                        <div class="date-time">
                            <i class="far fa-clock me-2"></i>
                            <?php echo date('l, F j, Y'); ?>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card bg-primary">
                                <div class="stat-card-inner">
                                    <div class="stat-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3>$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                                        <p>Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-success">
                                <div class="stat-card-inner">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3><?php echo $user_stats['student'] ?? 0; ?></h3>
                                        <p>Total Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-info">
                                <div class="stat-card-inner">
                                    <div class="stat-icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3><?php echo $user_stats['instructor'] ?? 0; ?></h3>
                                        <p>Instructors</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-warning">
                                <div class="stat-card-inner">
                                    <div class="stat-icon">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3><?php echo $active_lessons; ?></h3>
                                        <p>Active Lessons</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Bookings</h5>
                                <a href="manage-bookings.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Lesson</th>
                                            <th>Instructor</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['lesson_title']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['instructor_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    echo date('M j, Y', strtotime($booking['booking_date'])) . '<br>';
                                                    echo '<small class="text-muted">' . 
                                                         date('g:i A', strtotime($booking['booking_time'])) . 
                                                         '</small>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Confirmed</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin Dashboard Styles */
.admin-dashboard {
    min-height: 100vh;
    background-color: #f8f9fa;
}

/* Sidebar Styles */
.sidebar {
    min-height: 100vh;
    background: #212529;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.sidebar-sticky {
    position: sticky;
    top: 0;
    height: 100vh;
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.admin-profile {
    padding: 2rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.admin-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.sidebar .nav-link {
    color: rgba(255,255,255,.8);
    padding: 1rem 1.5rem;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

/* Main Content Styles */
.main-content {
    margin-left: 16.666667%;
    padding-top: 2rem;
}

/* Stats Cards */
.stat-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
}

.stat-card-inner {
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
}

.stat-details h3 {
    font-size: 1.5rem;
    margin: 0;
    font-weight: 600;
}

.stat-details p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.875rem;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .sidebar {
        position: static;
        min-height: auto;
        height: auto;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar-sticky {
        height: auto;
    }
}
</style>

<?php include '../includes/footer.php'; ?>