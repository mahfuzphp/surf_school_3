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
        <h2>My Bookings</h2>
        <a href="../lessons.php" class="btn btn-primary">Book New Lesson</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <h3 class="text-muted mb-3">No Bookings Yet</h3>
                <p class="mb-3">You haven't booked any lessons yet.</p>
                <a href="../lessons.php" class="btn btn-primary">Browse Available Lessons</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bookings as $booking): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($booking['lesson_title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($booking['description']); ?></p>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <p class="mb-1">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-clock me-2"></i>
                                        <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-tag me-2"></i>
                                        $<?php echo htmlspecialchars($booking['price']); ?>
                                    </p>
                                </div>

                                <?php
                                $today = new DateTime();
                                $bookingDate = new DateTime($booking['booking_date']);
                                $interval = $today->diff($bookingDate);
                                $daysUntil = $interval->days;

                                // Show cancel button only for future bookings more than 24 hours away
                                if ($bookingDate > $today && $daysUntil > 1):
                                ?>
                                    <form action="cancel-booking.php" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger">
                                            Cancel Booking
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