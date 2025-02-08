<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

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
            $stmt = $pdo->query("SELECT * FROM lessons ORDER BY created_at DESC LIMIT 3");
            while ($lesson = $stmt->fetch()): ?>
                <div class="col-md-4">
                    <div class="card h-100 card-hover">
                        <img src="assets/images/lessons/default.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($lesson['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text"><strong>$<?php echo htmlspecialchars($lesson['price']); ?></strong></p>
                            <a href="lesson-details.php?id=<?php echo $lesson['id']; ?>" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>