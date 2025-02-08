<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

// Get all active instructors
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE user_type = 'instructor' 
    ORDER BY created_at DESC 
    LIMIT 3
");
$instructors = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section-small">
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <h1 class="display-4 fw-bold">About Our Surf School</h1>
        <p class="lead">Learn about our story and meet our amazing instructors</p>
    </div>
</section>

<!-- About Section -->
<div class="container mt-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="pe-lg-4">
                <h2 class="mb-4">Our Story</h2>
                <p class="lead">Welcome to the best surf school in the area! We're passionate about teaching surfing and sharing the joy of riding waves with everyone.</p>

                <p>Our school was founded in 2010 by a group of professional surfers who wanted to share their love of surfing with others. Since then, we've taught thousands of students from beginners to advanced surfers.</p>

                <div class="mt-4">
                    <h3 class="h4">Our Mission</h3>
                    <p>To provide safe, fun, and professional surfing instruction while promoting ocean awareness and environmental responsibility.</p>
                </div>

                <div class="mt-4">
                    <h3 class="h4">Our Values</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Safety First</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Quality Instruction</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Environmental Responsibility</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Inclusive Community</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="position-relative">
                <img src="assets/images/about.png"
                    alt="Surf School Team"
                    class="img-fluid rounded shadow-lg">
                <div class="position-absolute bottom-0 end-0 bg-primary text-white p-3 rounded-top">
                    <h4 class="h5 mb-0">10+ Years of Experience</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructors Section -->
    <section class="py-5 mt-5">
        <h2 class="text-center mb-5">Meet Our Instructors</h2>
        <div class="row g-4">
            <?php foreach ($instructors as $instructor): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm hover-card">
                        <div class="text-center pt-4">
                            <?php if ($instructor['profile_image']): ?>
                                <img src="/uploads/profile_images/<?php echo htmlspecialchars($instructor['profile_image']); ?>"
                                    class="rounded-circle mb-3"
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                    alt="<?php echo htmlspecialchars($instructor['username']); ?>">
                            <?php else: ?>
                                <img src="/assets/images/instructor_1.png"
                                    class="rounded-circle mb-3"
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                    alt="<?php echo htmlspecialchars($instructor['username']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($instructor['username']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($instructor['profile_description'] ?? 'Professional surf instructor'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<style>
    .hero-section-small {
        height: 40vh;
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('/assets/images/about.png') center/cover;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-top: -70px;
        padding-top: 70px;
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