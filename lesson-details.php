<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/get_weather.php';

// Get lesson ID from URL
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch lesson details
$stmt = $pdo->prepare("
    SELECT l.*, u.username as instructor_name, u.profile_description as instructor_bio, u.profile_image 
    FROM lessons l 
    JOIN users u ON l.instructor_id = u.id 
    WHERE l.id = ? AND l.is_active = 1
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

// If lesson not found, redirect to lessons page
if (!$lesson) {
    $_SESSION['error_message'] = "Lesson not found";
    header("Location: lessons.php");
    exit();
}

// Get weather data
$weather = fetchWeatherData();
?>

<!-- Hero Section with Lesson Image -->
<section class="lesson-hero">
    <?php if ($lesson['lesson_image']): ?>
        <img src="/uploads/lesson_images/<?php echo htmlspecialchars($lesson['lesson_image']); ?>"
            alt="<?php echo htmlspecialchars($lesson['title']); ?>"
            class="lesson-hero-image">
    <?php else: ?>
        <img src="/assets/images/lesson.jpg"
            alt="<?php echo htmlspecialchars($lesson['title']); ?>"
            class="lesson-hero-image">
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($lesson['title']); ?></h1>
        <span class="badge bg-primary fs-5"><?php echo htmlspecialchars($lesson['level']); ?></span>
    </div>
</section>

<!-- Weather Widget Section -->
<section class="py-4 bg-light">
    <div class="container">
        <?php if ($weather): ?>
            <?php echo getWeatherDisplay($weather); ?>
        <?php endif; ?>
    </div>
</section>

<!-- Lesson Details Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h4 mb-4">About This Lesson</h2>
                        <p class="lead"><?php echo nl2br(htmlspecialchars($lesson['description'])); ?></p>

                        <div class="mt-4">
                            <h3 class="h5 mb-3">What You'll Learn</h3>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Proper surfing techniques and stance</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Wave reading and selection</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Ocean safety and awareness</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Equipment handling and care</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <h3 class="h5 mb-3">Requirements</h3>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info-circle text-primary me-2"></i>Basic swimming ability required</li>
                                <li><i class="fas fa-info-circle text-primary me-2"></i>Comfortable in ocean water</li>
                                <li><i class="fas fa-info-circle text-primary me-2"></i>Minimum age: 12 years</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Instructor Section -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 mb-4">Your Instructor</h2>
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($lesson['profile_image'])): ?>
                                <img src="/uploads/profile_images/<?php echo htmlspecialchars($lesson['profile_image']); ?>"
                                    alt="<?php echo htmlspecialchars($lesson['instructor_name']); ?>"
                                    class="rounded-circle me-3" style="width: 64px; height: 64px; object-fit: cover;">
                            <?php else: ?>
                                <img src="/assets/images/instructor-placeholder.jpg"
                                    alt="<?php echo htmlspecialchars($lesson['instructor_name']); ?>"
                                    class="rounded-circle me-3" style="width: 64px; height: 64px; object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <h3 class="h5 mb-1"><?php echo htmlspecialchars($lesson['instructor_name']); ?></h3>
                                <p class="text-muted mb-0">Professional Surf Instructor</p>
                            </div>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($lesson['instructor_bio'] ?? 'Experienced surf instructor passionate about teaching and water safety.')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h3 class="h4 mb-4">Lesson Details</h3>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="h2 text-primary mb-0">$<?php echo number_format($lesson['price'], 2); ?></h4>
                            <span class="badge bg-success">Available</span>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                Duration: 2 hours
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-users text-muted me-2"></i>
                                Group Size: Max 6 people
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-graduation-cap text-muted me-2"></i>
                                Level: <?php echo htmlspecialchars($lesson['level']); ?>
                            </li>
                        </ul>

                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student'): ?>
                            <a href="/student/book-lesson.php?lesson_id=<?php echo $lesson_id; ?>"
                                class="btn btn-primary btn-lg w-100 mb-3">
                                Book Now
                            </a>
                        <?php else: ?>
                            <a href="/login.php" class="btn btn-primary btn-lg w-100 mb-3">
                                Login to Book
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .lesson-hero {
        position: relative;
        height: 60vh;
        margin-top: -70px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .lesson-hero-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 0 1rem;
    }

    @media (max-width: 768px) {
        .lesson-hero {
            height: 40vh;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>