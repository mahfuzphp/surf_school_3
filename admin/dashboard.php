<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

include '../config/database.php';

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_students' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'student'")->fetchColumn(),
    'total_instructors' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'instructor'")->fetchColumn(),
    'total_lessons' => $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn(),
    'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'revenue' => $pdo->query("SELECT SUM(l.price) FROM bookings b JOIN lessons l ON b.lesson_id = l.id")->fetchColumn()
];

// Get recent bookings
$recent_bookings = $pdo->query("
    SELECT b.*, u.username, l.title as lesson_title 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN lessons l ON b.lesson_id = l.id 
    ORDER BY b.booking_date DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="container mt-5 pt-5">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row g-4 mb-4">
        <!-- Statistics Cards -->
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-6"><?php echo $stats['total_users']; ?></p>
                    <div class="mt-2">
                        <small>Students: <?php echo $stats['total_students']; ?></small><br>
                        <small>Instructors: <?php echo $stats['total_instructors']; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Lessons</h5>
                    <p class="card-text display-6"><?php echo $stats['total_lessons']; ?></p>
                    <small>Active Lessons Available</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text display-6">$<?php echo number_format($stats['revenue'] ?? 0, 2); ?></p>
                    <small>From <?php echo $stats['total_bookings']; ?> Bookings</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="list-group">
                        <a href="add-user.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus me-2"></i> Add New User
                        </a>
                        <a href="add-lesson.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-book-open me-2"></i> Add New Lesson
                        </a>
                        <a href="add-booking.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-plus me-2"></i> Create Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Bookings</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Lesson</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['lesson_title']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <a href="edit-booking.php?id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">View</a>
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

<?php include '../includes/footer.php'; ?>