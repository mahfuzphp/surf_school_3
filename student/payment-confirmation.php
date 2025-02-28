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

// Get booking details
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id) {
    $_SESSION['error_message'] = "Invalid booking ID";
    header("Location: /student/my-bookings.php");
    exit();
}

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, l.title as lesson_title, l.price, u.username as instructor_name 
    FROM bookings b
    JOIN lessons l ON b.lesson_id = l.id
    JOIN users u ON l.instructor_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error_message'] = "Booking not found or access denied";
    header("Location: /student/my-bookings.php");
    exit();
}

// Generate a random confirmation number
$confirmation_number = strtoupper(substr(md5($booking_id . time()), 0, 8));

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="confirmation-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Animation -->
                <div class="text-center mb-5">
                    <div class="success-animation">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                        </svg>
                    </div>
                    <h1 class="display-4 fw-bold mt-4">Payment Successful!</h1>
                    <p class="lead text-muted">Your surf lesson booking has been confirmed.</p>
                </div>

                <!-- Confirmation Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4">
                    <div class="card-header bg-gradient-success text-white p-4 rounded-top-4">
                        <h3 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3"></i>
                            Booking Confirmation
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="confirmation-number text-center mb-4 p-3 bg-light rounded-4">
                            <p class="text-muted mb-1">Confirmation Number</p>
                            <h2 class="mb-0 text-primary"><?php echo $confirmation_number; ?></h2>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Lesson Details</h5>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Lesson</p>
                                    <p class="fw-bold mb-0"><?php echo htmlspecialchars($booking['lesson_title']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Instructor</p>
                                    <p class="fw-bold mb-0"><?php echo htmlspecialchars($booking['instructor_name']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Price</p>
                                    <p class="fw-bold mb-0">$<?php echo number_format($booking['price'], 2); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Schedule</h5>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Date</p>
                                    <p class="fw-bold mb-0"><?php echo date('l, F j, Y', strtotime($booking['booking_date'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Time</p>
                                    <p class="fw-bold mb-0"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Status</p>
                                    <span class="badge bg-success px-3 py-2">Confirmed</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <h5 class="mb-3">Important Information</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-3"></i>
                                    <div>
                                        <strong>Location:</strong> Bondi Beach Surf School, North Bondi, Sydney
                                    </div>
                                </li>
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-clock text-primary mt-1 me-3"></i>
                                    <div>
                                        <strong>Arrival:</strong> Please arrive 15 minutes before your scheduled time
                                    </div>
                                </li>
                                <li class="mb-2 d-flex">
                                    <i class="fas fa-tshirt text-primary mt-1 me-3"></i>
                                    <div>
                                        <strong>What to Bring:</strong> Swimwear, towel, sunscreen, and water bottle
                                    </div>
                                </li>
                                <li class="d-flex">
                                    <i class="fas fa-info-circle text-primary mt-1 me-3"></i>
                                    <div>
                                        <strong>Equipment:</strong> All surfing equipment will be provided
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                    <a href="/student/my-bookings.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-calendar-alt me-2"></i>View My Bookings
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-lg px-4" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Confirmation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .confirmation-container {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        min-height: 100vh;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4CAF50, #388E3C) !important;
    }

    .card {
        overflow: hidden;
    }

    .confirmation-number {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
    }

    .btn {
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Success Animation */
    .success-animation {
        margin: 0 auto;
    }

    .checkmark {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #4CAF50;
        stroke-miterlimit: 10;
        box-shadow: 0 0 0 #4CAF50;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #4CAF50;
        fill: none;
        animation: stroke .6s cubic-bezier(0.650, 0.000, 0.450, 1.000) forwards;
    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke .3s cubic-bezier(0.650, 0.000, 0.450, 1.000) .8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {

        0%,
        100% {
            transform: none;
        }

        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: 0 0 0 30px rgba(76, 175, 80, 0.1);
        }
    }

    /* Print Styles */
    @media print {

        .navbar,
        .footer,
        .btn {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }

        .card-header {
            background: #f8f9fa !important;
            color: #212529 !important;
        }

        body {
            background: white !important;
        }

        .confirmation-container {
            background: white !important;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>