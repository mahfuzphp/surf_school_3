<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

checkLogin();

// Check if user is instructor
if ($_SESSION['user_type'] !== 'instructor') {
    header('Location: ../index.php');
    exit();
}

include '../config/database.php';

// Get instructor's details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$instructor = $stmt->fetch();

// Get upcoming lessons/bookings
$stmt = $pdo->prepare("
    SELECT b.*, u.username, u.email, u.profile_image, l.title as lesson_title, l.price
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN lessons l ON b.lesson_id = l.id 
    WHERE b.booking_date >= CURDATE()
    ORDER BY b.booking_date ASC, b.booking_time ASC
    LIMIT 5
");
$stmt->execute();
$upcoming_lessons = $stmt->fetchAll();

// Get statistics
$stats = [
    'total_students' => $pdo->query("SELECT COUNT(DISTINCT user_id) FROM bookings")->fetchColumn(),
    'total_lessons' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'total_earnings' => $pdo->query("SELECT SUM(price) FROM bookings b JOIN lessons l ON b.lesson_id = l.id")->fetchColumn()
];
?>

<div class="container mt-5 pt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Welcome, <?php echo htmlspecialchars($instructor['username']); ?>!</h2>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="my-lessons.php" class="btn btn-primary">View All Lessons</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Statistics Cards -->
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <p class="card-text display-6"><?php echo $stats['total_students']; ?></p>
                    <small>Students Taught</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Lessons</h5>
                    <p class="card-text display-6"><?php echo $stats['total_lessons']; ?></p>
                    <small>Lessons Given</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Earnings</h5>
                    <p class="card-text display-6">$<?php echo number_format($stats['total_earnings'] ?? 0, 2); ?></p>
                    <small>From All Lessons</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profile Overview</h5>
                    <?php if ($instructor['profile_image']): ?>
                        <img src="../uploads/profile_images/<?php echo htmlspecialchars($instructor['profile_image']); ?>"
                            class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                    <?php endif; ?>
                    <p class="card-text">
                        <strong>Email:</strong> <?php echo htmlspecialchars($instructor['email']); ?><br>
                        <strong>Member Type:</strong> <?php echo ucfirst(htmlspecialchars($instructor['user_type'])); ?><br>
                        <strong>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($instructor['profile_description'] ?? 'No description available')); ?>
                    </p>
                    <a href="profile.php" class="btn btn-outline-primary">Edit Profile</a>
                </div>
            </div>
        </div>

        <!-- Upcoming Lessons -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Lessons</h5>
                    <?php if (empty($upcoming_lessons)): ?>
                        <p class="text-muted">No upcoming lessons scheduled.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Lesson</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_lessons as $lesson): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($lesson['profile_image']): ?>
                                                        <img src="../uploads/profile_images/<?php echo htmlspecialchars($lesson['profile_image']); ?>"
                                                            class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($lesson['username']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($lesson['lesson_title']); ?></td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($lesson['booking_date'])); ?><br>
                                                <small><?php echo date('g:i A', strtotime($lesson['booking_time'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Confirmed</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>