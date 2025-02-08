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
    handleError("Invalid request method", "/lessons.php");
}

// Get and validate form data
$lesson_id = (int)$_POST['lesson_id'];
$booking_date = trim($_POST['booking_date']);
$booking_time = trim($_POST['booking_time']);

// Validate required fields
if (!$lesson_id || !$booking_date || !$booking_time) {
    handleError("All fields are required", "/student/book-lesson.php?id=$lesson_id");
}

try {
    // Check if lesson exists and is active
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND is_active = 1");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();

    if (!$lesson) {
        handleError("Invalid lesson selected", "/lessons.php");
    }

    // Validate booking date is in the future
    $booking_datetime = new DateTime($booking_date . ' ' . $booking_time);
    $now = new DateTime();

    if ($booking_datetime <= $now) {
        handleError("Booking must be for a future date and time", "/student/book-lesson.php?id=$lesson_id");
    }

    // Check if the time slot is available
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM bookings 
        WHERE lesson_id = ? 
        AND booking_date = ? 
        AND booking_time = ?
    ");
    $stmt->execute([$lesson_id, $booking_date, $booking_time]);
    
    if ($stmt->fetchColumn() > 0) {
        handleError("This time slot is already booked. Please choose another time.", 
                   "/student/book-lesson.php?id=$lesson_id");
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Create the booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, lesson_id, booking_date, booking_time, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $lesson_id,
        $booking_date,
        $booking_time
    ]);

    // Commit transaction
    $pdo->commit();

    // Redirect with success message
    handleSuccess("Lesson booked successfully!", "/student/my-bookings.php");

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    handleError("An error occurred while processing your booking. Please try again.", 
                "/student/book-lesson.php?id=$lesson_id");
} 