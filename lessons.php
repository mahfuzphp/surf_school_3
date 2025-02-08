<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <h1 class="text-center mb-5">Our Surfing Lessons</h1>

    <div class="row g-4">
        <?php
        include 'config/database.php';
        $stmt = $pdo->query("SELECT * FROM lessons ORDER BY price ASC");
        while ($lesson = $stmt->fetch()): ?>
            <div class="col-md-4">
                <div class="card h-100 card-hover">
                    <img src="assets/images/lessons/default.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($lesson['description']); ?></p>
                        <p class="card-text"><strong>$<?php echo htmlspecialchars($lesson['price']); ?></strong></p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="student/book-lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary">Book Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>