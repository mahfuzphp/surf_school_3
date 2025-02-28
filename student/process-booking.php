<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

checkLogin();

// Check if user is a student
if ($_SESSION['user_type'] !== 'student') {
    $_SESSION['error_message'] = "Access denied. Student privileges required.";
    header("Location: /login.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $booking_date = isset($_POST['booking_date']) ? $_POST['booking_date'] : '';
    $booking_time = isset($_POST['booking_time']) ? $_POST['booking_time'] : '';

    // Validate inputs
    if (!$lesson_id || !$booking_date || !$booking_time) {
        $_SESSION['error_message'] = "All fields are required";
        header("Location: /student/book-lesson.php?id=$lesson_id");
        exit();
    }

    // Check if the date is valid
    if (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error_message'] = "Please select a future date";
        header("Location: /student/book-lesson.php?id=$lesson_id");
        exit();
    }

    // Check if the booking already exists
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings 
        WHERE user_id = ? AND lesson_id = ? AND booking_date = ? AND booking_time = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $lesson_id, $booking_date, $booking_time]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $_SESSION['error_message'] = "You already have a booking for this lesson at this time";
        header("Location: /student/book-lesson.php?id=$lesson_id");
        exit();
    }

    // Check if the slot is available (not exceeding max_students)
    $stmt = $pdo->prepare("
        SELECT l.max_students, COUNT(b.id) as current_bookings
        FROM lessons l
        LEFT JOIN bookings b ON l.id = b.lesson_id AND b.booking_date = ? AND b.booking_time = ? AND b.status != 'cancelled'
        WHERE l.id = ?
        GROUP BY l.id
    ");
    $stmt->execute([$booking_date, $booking_time, $lesson_id]);
    $result = $stmt->fetch();

    if ($result && $result['current_bookings'] >= $result['max_students']) {
        $_SESSION['error_message'] = "This time slot is fully booked. Please select another time.";
        header("Location: /student/book-lesson.php?id=$lesson_id");
        exit();
    }

    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, lesson_id, booking_date, booking_time, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");

    try {
        $stmt->execute([$_SESSION['user_id'], $lesson_id, $booking_date, $booking_time]);
        $booking_id = $pdo->lastInsertId();

        // Store the booking ID in session for the payment page
        $_SESSION['last_booking_id'] = $booking_id;

        // Redirect to payment page
        header("Location: /student/payment.php?booking_id=$booking_id");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error creating booking: " . $e->getMessage();
        header("Location: /student/book-lesson.php?id=$lesson_id");
        exit();
    }
} else {
    // If not a POST request, redirect to lessons page
    header("Location: /lessons.php");
    exit();
}
