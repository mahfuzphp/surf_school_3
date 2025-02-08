<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/get_weather.php';

// Get weather data for the surf conditions
$weather = fetchWeatherData();

// Get all active lessons
require_once 'config/database.php';
$stmt = $pdo->query("
    SELECT l.*, u.username as instructor_name 
    FROM lessons l 
    JOIN users u ON l.instructor_id = u.id 
    WHERE l.is_active = 1 
    ORDER BY l.level ASC
");
$lessons = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section-small">
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <h1 class="display-4 fw-bold">Our Surf Lessons</h1>
        <p class="lead">Choose the perfect lesson for your skill level</p>
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

<!-- Lessons Section -->
<section class="py-5">
    <div class="container">
        <!-- Level Filter Buttons -->
        <div class="text-center mb-5">
            <div class="btn-group" role="group" aria-label="Lesson level filter">
                <button type="button" class="btn btn-outline-primary active" data-level="all">All Levels</button>
                <button type="button" class="btn btn-outline-primary" data-level="Beginner">Beginner</button>
                <button type="button" class="btn btn-outline-primary" data-level="Intermediate">Intermediate</button>
                <button type="button" class="btn btn-outline-primary" data-level="Advanced">Advanced</button>
            </div>
        </div>

        <!-- Lessons Grid -->
        <div class="row g-4">
            <?php foreach ($lessons as $lesson): ?>
                <div class="col-md-6 col-lg-4 lesson-card" data-level="<?php echo htmlspecialchars($lesson['level']); ?>">
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
                            <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(substr($lesson['description'], 0, 100)) . '...'; ?></p>

                            <div class="lesson-details mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                    <span><?php echo htmlspecialchars($lesson['instructor_name']); ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <span><?php echo htmlspecialchars($lesson['duration']); ?> minutes</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <span>Max <?php echo htmlspecialchars($lesson['max_students']); ?> students</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="text-primary mb-0">$<?php echo htmlspecialchars($lesson['price']); ?></h4>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="/student/book-lesson.php?id=<?php echo $lesson['id']; ?>"
                                        class="btn btn-primary">Book Now</a>
                                <?php else: ?>
                                    <a href="/login.php" class="btn btn-outline-primary">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .hero-section-small {
        height: 40vh;
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
            url('/assets/images/lesson.jpeg') center/cover;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-top: -70px;
        padding-top: 70px;
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

    /* Level filter buttons styling */
    .btn-group .btn {
        padding: 0.5rem 1.5rem;
        border-radius: 30px !important;
        margin: 0 0.25rem;
    }

    .btn-group .btn.active {
        background-color: var(--bs-primary);
        color: white;
    }

    @media (max-width: 768px) {
        .hero-section-small {
            height: 30vh;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-group .btn {
            flex: 1 1 auto;
            min-width: 120px;
        }
    }
</style>

<script>
    // Level filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-level]');
        const lessonCards = document.querySelectorAll('.lesson-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const level = this.dataset.level;

                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter lessons
                lessonCards.forEach(card => {
                    if (level === 'all' || card.dataset.level === level) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>