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
    /* Enhanced Hero Section */
    .hero-section-small {
        height: 50vh;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
            url('/assets/images/lesson.jpeg') center/cover fixed;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-top: -70px;
        padding-top: 70px;
        overflow: hidden;
    }

    .hero-section-small::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100px;
        background: linear-gradient(to top, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-content h1 {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInDown 1s ease;
    }

    .hero-content p {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease;
    }

    /* Enhanced Card Styling */
    .lesson-image {
        height: 250px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .hover-card {
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: none;
        overflow: hidden;
    }

    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .hover-card:hover .lesson-image {
        transform: scale(1.05);
    }

    .lesson-details {
        font-size: 0.9rem;
        padding: 0.5rem 0;
    }

    .lesson-details i {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        margin-right: 0.75rem !important;
    }

    .badge {
        padding: 0.6em 1.2em;
        font-weight: 500;
        letter-spacing: 0.5px;
        border-radius: 30px;
    }

    /* Enhanced Filter Buttons */
    .btn-group {
        background: white;
        padding: 0.5rem;
        border-radius: 35px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .btn-group .btn {
        padding: 0.7rem 1.8rem;
        border-radius: 30px !important;
        margin: 0 0.25rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        border-width: 2px;
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-group .btn.active {
        background: linear-gradient(45deg, var(--bs-primary), #00a5b9);
        border-color: transparent;
        color: white;
        box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.3);
    }

    /* Card Content Styling */
    .card-body {
        padding: 1.5rem;
    }

    .card-title {
        font-weight: 600;
        color: #2c3e50;
    }

    .text-primary {
        color: var(--bs-primary) !important;
    }

    .btn-primary,
    .btn-outline-primary {
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-section-small {
            height: 40vh;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem;
        }

        .btn-group .btn {
            flex: 1 1 auto;
            min-width: 140px;
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
        }

        .lesson-image {
            height: 200px;
        }

        .card-body {
            padding: 1.25rem;
        }
    }

    /* Add smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--bs-primary);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
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