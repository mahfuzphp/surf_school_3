<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section-small">
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <h1 class="display-4 fw-bold">Contact Us</h1>
        <p class="lead">We're here to help and answer any questions you might have</p>
    </div>
</section>

<div class="container mt-5">
    <!-- Contact Cards -->
    <div class="row g-4 justify-content-center mb-5">
        <div class="col-md-4">
            <div class="card h-100 text-center hover-card">
                <div class="card-body p-4">
                    <div class="icon-circle mb-4">
                        <i class="fas fa-phone fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Call Us</h3>
                    <p class="mb-2">Mon - Fri: 8:00 AM - 6:00 PM</p>
                    <p class="mb-0"><strong>(123) 456-7890</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center hover-card">
                <div class="card-body p-4">
                    <div class="icon-circle mb-4">
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Email Us</h3>
                    <p class="mb-2">We'll respond within 24 hours</p>
                    <p class="mb-0"><strong>info@surfschool.com</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 text-center hover-card">
                <div class="card-body p-4">
                    <div class="icon-circle mb-4">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <h3 class="h4 mb-3">Visit Us</h3>
                    <p class="mb-2">123 Beach Road</p>
                    <p class="mb-0"><strong>Surf City, SC 12345</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Hours Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Business Hours</h2>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-end pe-4"><strong>Monday - Friday:</strong></td>
                                    <td>8:00 AM - 6:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="text-end pe-4"><strong>Saturday:</strong></td>
                                    <td>9:00 AM - 5:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="text-end pe-4"><strong>Sunday:</strong></td>
                                    <td>10:00 AM - 4:00 PM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="card shadow-sm overflow-hidden">
        <div class="ratio ratio-21x9">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.835434509374!2d144.95373631531978!3d-37.817327679751685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d4c2b349649%3A0xb6899234e561db11!2sEnvato!5e0!3m2!1sen!2sus!4v1644933736974!5m2!1sen!2sus"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<style>
    .hero-section-small {
        height: 40vh;
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('/assets/images/contact.png') center/cover;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-top: -70px;
        padding-top: 70px;
    }

    .icon-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    @media (max-width: 768px) {
        .hero-section-small {
            height: 30vh;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>