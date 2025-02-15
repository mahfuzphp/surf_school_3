<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/functions.php';

checkLogin();

include '../config/database.php';

// Get user's bookings with lesson details
$stmt = $pdo->prepare("
    SELECT b.*, l.title as lesson_title, l.price, l.description
    FROM bookings b 
    JOIN lessons l ON b.lesson_id = l.id 
    WHERE b.user_id = ? 
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="display-6 fw-bold">My Bookings</h2>
        <a href="../lessons.php" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus-circle me-2"></i>Book New Lesson
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <img src="../assets/images/no-bookings.svg" alt="No bookings" class="mb-4" style="width: 200px;">
                <h3 class="text-muted mb-3">No Bookings Yet</h3>
                <p class="text-muted mb-4">Start your learning journey by booking your first lesson!</p>
                <a href="../lessons.php" class="btn btn-primary btn-lg rounded-pill px-5">Browse Available Lessons</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bookings as $booking):
                $today = new DateTime();
                $bookingDate = new DateTime($booking['booking_date']);
                $bookingDateTime = new DateTime($booking['booking_date'] . ' ' . $booking['booking_time']);
                $interval = $today->diff($bookingDate);
                $daysUntil = $interval->days;

                // Determine booking status
                $status = '';
                $statusClass = '';
                if ($bookingDateTime < $today) {
                    $status = 'Completed';
                    $statusClass = 'bg-success';
                } elseif ($daysUntil <= 1) {
                    $status = 'Upcoming Soon';
                    $statusClass = 'bg-warning text-dark';
                } else {
                    $status = 'Scheduled';
                    $statusClass = 'bg-primary';
                }
            ?>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0 position-relative">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 py-2">
                                <?php echo $status; ?>
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($booking['lesson_title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($booking['description']); ?></p>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <p class="mb-2 text-primary">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?>
                                    </p>
                                    <p class="mb-2 text-primary">
                                        <i class="fas fa-clock me-2"></i>
                                        <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                                    </p>
                                    <p class="mb-0 fw-bold fs-5">
                                        <i class="fas fa-tag me-2"></i>
                                        $<?php echo htmlspecialchars($booking['price']); ?>
                                    </p>
                                </div>

                                <?php if ($bookingDate > $today && $daysUntil > 1): ?>
                                    <form action="cancel-booking.php" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                            <i class="fas fa-times-circle me-2"></i>Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>