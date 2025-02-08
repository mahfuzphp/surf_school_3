<?php
// Start session before any output
session_start();
require_once 'includes/functions.php';

// Set page title
$pageTitle = "Privacy Policy - Surf School";

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 privacy-card">
                <div class="card-header bg-gradient-primary text-white p-5 text-center">
                    <i class="fas fa-shield-alt fa-3x mb-3"></i>
                    <h1 class="h2 mb-2">Privacy Policy</h1>
                    <p class="mb-0 text-white-50">Last updated: <?php echo date('F j, Y'); ?></p>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">1. Introduction</h2>
                        <div class="bg-light p-4 rounded">
                            <p class="mb-0">At Surf School ("we", "our", or "us"), we are committed to protecting your privacy and personal information in accordance with the Privacy Act 1988 (Cth) and the Australian Privacy Principles (APPs).</p>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">2. Information We Collect</h2>
                        <div class="bg-light p-4 rounded">
                            <p>We collect the following types of personal information:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-user-circle text-primary me-2"></i>Name and contact details</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-calendar-alt text-primary me-2"></i>Date of birth</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-phone-alt text-primary me-2"></i>Emergency contact information</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-swimming-pool text-primary me-2"></i>Swimming ability and surfing experience</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-notes-medical text-primary me-2"></i>Medical conditions relevant to surfing activities</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-credit-card text-primary me-2"></i>Payment information</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-camera text-primary me-2"></i>Photos and videos during lessons (with consent)</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-history text-primary me-2"></i>Booking history and preferences</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">3. How We Use Your Information</h2>
                        <div class="bg-light p-4 rounded">
                            <p>We use your personal information to:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-check-circle text-primary me-2"></i>Provide surfing lessons and related services</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-shield-alt text-primary me-2"></i>Ensure your safety during activities</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-credit-card text-primary me-2"></i>Process bookings and payments</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-envelope text-primary me-2"></i>Send important updates about your lessons</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-file-alt text-primary me-2"></i>Maintain appropriate records</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-chart-line text-primary me-2"></i>Improve our services</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-balance-scale text-primary me-2"></i>Comply with legal obligations</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">4. Information Security</h2>
                        <div class="bg-light p-4 rounded">
                            <p>We implement appropriate security measures to protect your personal information from:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-user-secret text-primary me-2"></i>Unauthorized access</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-edit text-primary me-2"></i>Modification</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-eye-slash text-primary me-2"></i>Disclosure</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Misuse or loss</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">5. Sharing Your Information</h2>
                        <div class="bg-light p-4 rounded">
                            <p>We may share your information with:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-user-tie text-primary me-2"></i>Our instructors and staff</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-first-aid text-primary me-2"></i>Insurance providers</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-ambulance text-primary me-2"></i>Emergency services (if required)</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-gavel text-primary me-2"></i>Regulatory authorities</li>
                            </ul>
                            <p>We will not sell or rent your personal information to third parties for marketing purposes.</p>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">6. Your Rights</h2>
                        <div class="bg-light p-4 rounded">
                            <p>You have the right to:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-eye text-primary me-2"></i>Access your personal information</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-edit text-primary me-2"></i>Request corrections to your information</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-times-circle text-primary me-2"></i>Opt-out of marketing communications</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-exclamation-circle text-primary me-2"></i>File a complaint about how we handle your information</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">7. Cookies and Tracking</h2>
                        <div class="bg-light p-4 rounded">
                            <p>Our website uses cookies and similar technologies to:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-cookie-bite text-primary me-2"></i>Improve user experience</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-history text-primary me-2"></i>Remember your preferences</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-chart-bar text-primary me-2"></i>Analyze website traffic</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-lock text-primary me-2"></i>Maintain security</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">8. Children's Privacy</h2>
                        <div class="bg-light p-4 rounded">
                            <p>For students under 18 years of age:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-child text-primary me-2"></i>We require parental/guardian consent</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-users text-primary me-2"></i>Parents/guardians can access and control their child's information</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-lock text-primary me-2"></i>Special protections are in place for storing and handling children's data</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">9. Changes to This Policy</h2>
                        <div class="bg-light p-4 rounded">
                            <p>We may update this privacy policy periodically. Significant changes will be notified via:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent"><i class="fas fa-envelope text-primary me-2"></i>Email notification</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-globe text-primary me-2"></i>Website announcement</li>
                                <li class="list-group-item bg-transparent"><i class="fas fa-map-marker-alt text-primary me-2"></i>Notice at our physical location</li>
                            </ul>
                        </div>
                    </div>

                    <div class="privacy-section mb-5">
                        <h2 class="h4 text-primary mb-4">10. Contact Us</h2>
                        <div class="bg-light p-4 rounded">
                            <p>For privacy-related inquiries:</p>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="contact-card text-center p-4 bg-white rounded shadow-sm">
                                        <i class="fas fa-envelope text-primary fa-2x mb-3"></i>
                                        <h5>Email</h5>
                                        <p class="mb-0">privacy@surfschool.com</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="contact-card text-center p-4 bg-white rounded shadow-sm">
                                        <i class="fas fa-phone text-primary fa-2x mb-3"></i>
                                        <h5>Phone</h5>
                                        <p class="mb-0">1300 SURF AU</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="contact-card text-center p-4 bg-white rounded shadow-sm">
                                        <i class="fas fa-map-marker-alt text-primary fa-2x mb-3"></i>
                                        <h5>Address</h5>
                                        <p class="mb-0">123 Beach Road<br>Bondi Beach, NSW 2026</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Move styles to a separate CSS file -->
<link rel="stylesheet" href="/assets/css/privacy-policy.css">

<!-- Add breadcrumb navigation -->
<nav aria-label="breadcrumb" class="container mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
    </ol>
</nav>

<!-- Add print functionality -->
<div class="container mb-4">
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="fas fa-print me-2"></i>Print Policy
    </button>
</div>

<!-- Add back to top button -->
<button id="backToTop" class="btn btn-primary back-to-top" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Add JavaScript for back to top functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.getElementById('backToTop');

        // Show/hide button based on scroll position
        window.onscroll = function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                backToTopButton.style.display = "block";
            } else {
                backToTopButton.style.display = "none";
            }
        };

        // Smooth scroll to top
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>