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

// Get booking details from session or query parameters
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id && isset($_SESSION['last_booking_id'])) {
    $booking_id = $_SESSION['last_booking_id'];
}

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

// Process payment
$payment_success = false;
$payment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate payment processing
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';

    // Basic validation
    if ($payment_method === 'credit_card') {
        if (empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
            $payment_error = "Please fill in all card details";
        } else if (strlen($card_number) < 16) {
            $payment_error = "Invalid card number";
        } else {
            // Simulate successful payment
            $payment_success = true;

            // Update booking status to confirmed
            $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $update_stmt->execute([$booking_id]);

            // Set success message
            $_SESSION['success_message'] = "Payment successful! Your booking is now confirmed.";
        }
    } else if ($payment_method === 'paypal') {
        // Simulate PayPal payment
        $payment_success = true;

        // Update booking status to confirmed
        $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $update_stmt->execute([$booking_id]);

        // Set success message
        $_SESSION['success_message'] = "PayPal payment successful! Your booking is now confirmed.";
    }

    if ($payment_success) {
        header("Location: /student/payment-confirmation.php?booking_id=" . $booking_id);
        exit();
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="payment-container">
    <div class="container py-5">
        <!-- Payment Header -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">Complete Your Payment</h1>
            <p class="lead text-muted">Secure payment for your surf lesson booking</p>
        </div>

        <div class="row g-5">
            <!-- Order Summary -->
            <div class="col-lg-4 order-lg-2">
                <div class="card border-0 shadow-lg rounded-4 sticky-lg-top" style="top: 100px;">
                    <div class="card-header bg-gradient-primary text-white p-4 rounded-top-4">
                        <h3 class="mb-0">Order Summary</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3"><?php echo htmlspecialchars($booking['lesson_title']); ?></h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Date:</span>
                                <span class="fw-bold"><?php echo date('D, M j, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Time:</span>
                                <span class="fw-bold"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Instructor:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($booking['instructor_name']); ?></span>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Lesson Price:</span>
                                <span>$<?php echo number_format($booking['price'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Booking Fee:</span>
                                <span>$<?php echo number_format($booking['price'] * 0.05, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 mt-3">
                                <span>Total:</span>
                                <span class="text-primary">$<?php echo number_format($booking['price'] * 1.05, 2); ?></span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <small>Secure Payment</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-undo text-success me-2"></i>
                                <small>Free Cancellation (24h before)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="col-lg-8 order-lg-1">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-gradient-success text-white p-4 rounded-top-4">
                        <h3 class="mb-0">Payment Details</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($payment_error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $payment_error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="payment-form">
                            <div class="mb-4">
                                <h5 class="mb-3">Select Payment Method</h5>
                                <div class="payment-methods">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="credit_card" value="credit_card" checked>
                                            <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4" for="credit_card">
                                                <i class="fas fa-credit-card fa-2x mb-3"></i>
                                                <span>Credit Card</span>
                                                <div class="mt-2">
                                                    <img src="/assets/images/visa.png" alt="Visa" height="25">
                                                    <img src="/assets/images/mastercard.png" alt="Mastercard" height="25">
                                                    <img src="/assets/images/amex.png" alt="Amex" height="25">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="radio" class="btn-check" name="payment_method" id="paypal" value="paypal">
                                            <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4" for="paypal">
                                                <i class="fab fa-paypal fa-2x mb-3"></i>
                                                <span>PayPal</span>
                                                <small class="text-muted mt-2">Pay with your PayPal account</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="credit-card-form" class="mt-4">
                                <h5 class="mb-3">Card Information</h5>
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-lg" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                        <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                    </div>
                                    <div class="form-text">For demo, use any 16-digit number</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="card_expiry" class="form-label">Expiration Date</label>
                                        <input type="text" class="form-control form-control-lg" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="card_cvv" class="form-label">Security Code (CVV)</label>
                                        <input type="text" class="form-control form-control-lg" id="card_cvv" name="card_cvv" placeholder="123">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control form-control-lg" id="card_name" name="card_name" placeholder="John Doe">
                                </div>
                            </div>

                            <div id="paypal-form" class="mt-4 d-none">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You will be redirected to PayPal to complete your payment.
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg py-3">
                                    <i class="fas fa-lock me-2"></i>Pay Now $<?php echo number_format($booking['price'] * 1.05, 2); ?>
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Your payment information is secure and encrypted
                                </small>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Payment Security</h5>
                        <p class="text-muted mb-0">
                            This is a demo payment page. In a real application, we would use a secure payment processor
                            like Stripe, PayPal, or Square to handle your payment information securely. No actual payment
                            will be processed in this demo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-container {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        min-height: 100vh;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #2196F3, #1976D2) !important;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4CAF50, #388E3C) !important;
    }

    .card {
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .payment-methods label {
        border-radius: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }

    .payment-methods label:hover {
        transform: translateY(-5px);
    }

    .btn-check:checked+label {
        background-color: rgba(33, 150, 243, 0.1);
        border-color: #2196F3;
        transform: translateY(-5px);
    }

    .form-control {
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        border: 2px solid #dee2e6;
    }

    .form-control:focus {
        border-color: #2196F3;
        box-shadow: 0 0 0 0.25rem rgba(33, 150, 243, 0.25);
    }

    .input-group-text {
        border-radius: 0 0.75rem 0.75rem 0;
        background-color: #f8f9fa;
    }

    .btn-success {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
        border: none;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
    }

    @media (max-width: 768px) {
        .sticky-lg-top {
            position: relative !important;
            top: 0 !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const creditCardRadio = document.getElementById('credit_card');
        const paypalRadio = document.getElementById('paypal');
        const creditCardForm = document.getElementById('credit-card-form');
        const paypalForm = document.getElementById('paypal-form');

        function togglePaymentForms() {
            if (creditCardRadio.checked) {
                creditCardForm.classList.remove('d-none');
                paypalForm.classList.add('d-none');
            } else {
                creditCardForm.classList.add('d-none');
                paypalForm.classList.remove('d-none');
            }
        }

        creditCardRadio.addEventListener('change', togglePaymentForms);
        paypalRadio.addEventListener('change', togglePaymentForms);

        // Format card number with spaces
        const cardNumberInput = document.getElementById('card_number');
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 16) {
                value = value.substr(0, 16);
            }

            // Add spaces every 4 digits
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            e.target.value = formattedValue;
        });

        // Format expiry date
        const expiryInput = document.getElementById('card_expiry');
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4);
            }

            if (value.length > 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }

            e.target.value = value;
        });

        // Limit CVV to 3 or 4 digits
        const cvvInput = document.getElementById('card_cvv');
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substr(0, 4);
            }
            e.target.value = value;
        });
    });
</script>

<?php include '../includes/footer.php'; ?>