<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php';
include 'includes/get_weather.php';

// Get weather data
$weather = fetchWeatherData();
?>

<!-- Hero Section -->
<section class="hero-section">
    <video class="hero-video" autoplay muted loop playsinline>
        <source src="assets/images/hero.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="display-4 fw-bold">Ride the Perfect Wave</h1>
        <p class="lead mb-4">Learn surfing from professional instructors in a safe and fun environment</p>
        <a href="lessons.php" class="btn btn-primary btn-lg hero-btn">Start Learning</a>
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

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Expert Instructors</h5>
                        <p class="card-text">Learn from certified professional surfers with years of experience.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Small Groups</h5>
                        <p class="card-text">Personal attention with small group sizes for better learning.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 card-hover">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Safety First</h5>
                        <p class="card-text">Top-notch equipment and safety measures for peace of mind.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Lessons Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Popular Lessons</h2>
        <div class="row g-4">
            <?php
            include 'config/database.php';
            $stmt = $pdo->query("
                SELECT l.*, u.username as instructor_name 
                FROM lessons l 
                JOIN users u ON l.instructor_id = u.id 
                WHERE l.is_active = 1 
                ORDER BY l.created_at DESC 
                LIMIT 3
            ");
            while ($lesson = $stmt->fetch()): ?>
                <div class="col-md-4">


                    <div class="card h-100 shadow-sm hover-card">
                        <?php if ($lesson['lesson_image']): ?>
                            <img src="/uploads/lesson_images/<?php echo htmlspecialchars($lesson['lesson_image']); ?>"
                                class="card-img-top lesson-image"
                                alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                        <?php else: ?>

                            <img src="/assets/images/lesson.jpg"
                                class="card-img-top lesson-image"
                                alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($lesson['level']); ?></span>
                            </div>
                            <p class="card-text text-muted"><?php echo htmlspecialchars(substr($lesson['description'], 0, 100)) . '...'; ?></p>

                            <div class="lesson-details mb-3">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <span><?php echo htmlspecialchars($lesson['instructor_name']); ?></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-primary mb-0">$<?php echo htmlspecialchars($lesson['price']); ?></h4>
                                <a href="/lesson-details.php?id=<?php echo $lesson['id']; ?>"
                                    class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Additional CSS for hero video -->
<style>
    .hero-section {
        position: relative;
        height: 80vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .hero-video {
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

    .hero-btn {
        padding: 0.8rem 2.5rem;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: transform 0.2s;
    }

    .hero-btn:hover {
        transform: translateY(-2px);
    }

    /* Weather widget specific styles for homepage */
    .weather-widget {
        margin: -3rem auto 0;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 768px) {
        .hero-section {
            height: 60vh;
        }

        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1rem;
        }

        .hero-btn {
            padding: 0.6rem 1.8rem;
            font-size: 1rem;
        }
    }

    .lesson-image {
        height: 200px;
        object-fit: cover;
    }

    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .lesson-details {
        font-size: 0.9rem;
    }

    .badge {
        padding: 0.5em 1em;
    }
</style>

<?php include 'includes/footer.php'; ?>