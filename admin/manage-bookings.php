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

// Handle booking deletion
if (isset($_POST['delete_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
}

// Get all bookings with user and lesson details
$stmt = $pdo->query("
    SELECT b.*, u.username, u.email, l.title as lesson_title, l.price 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN lessons l ON b.lesson_id = l.id 
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Bookings</h2>
        <a href="add-booking.php" class="btn btn-primary">Add New Booking</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Lesson</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($booking['username']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['lesson_title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                <td>$<?php echo htmlspecialchars($booking['price']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-booking.php?id=<?php echo $booking['id']; ?>"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="delete_booking"
                                                class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>