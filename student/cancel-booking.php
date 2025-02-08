<?php
session_start();
include '../includes/functions.php';
include '../config/database.php';

checkLogin();

// Check if user is a student
if ($_SESSION['user_type'] !== 'student') {
    handleError("Access denied", "/index.php");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Invalid request method", "/student/my-bookings.php");
}

// Get and validate booking ID
$booking_id = (int)$_POST['booking_id'];

if (!$booking_id) {
    handleError("Invalid booking ID", "/student/my-bookings.php");
}

try {
    // Get booking details and verify ownership
    $stmt = $pdo->prepare("
        SELECT b.*, l.title 
        FROM bookings b
        JOIN lessons l ON b.lesson_id = l.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        handleError("Booking not found or access denied", "/student/my-bookings.php");
    }

    // Check if booking is in the future and more than 24 hours away
    $booking_datetime = new DateTime($booking['booking_date'] . ' ' . $booking['booking_time']);
    $now = new DateTime();
    $interval = $now->diff($booking_datetime);
    
    if ($booking_datetime <= $now) {
        handleError("Cannot cancel past bookings", "/student/my-bookings.php");
    }
    
    if ($interval->days < 1) {
        handleError("Bookings can only be cancelled at least 24 hours in advance", "/student/my-bookings.php");
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Delete the booking
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);

    // Commit transaction
    $pdo->commit();

    // Redirect with success message
    handleSuccess("Your booking for '{$booking['title']}' has been cancelled", "/student/my-bookings.php");

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    handleError("An error occurred while cancelling your booking. Please try again.", 
                "/student/my-bookings.php");
} 