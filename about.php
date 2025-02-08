<?php
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="mb-4">About Our Surf School</h1>
            <p class="lead">Welcome to the best surf school in the area! We're passionate about teaching surfing and sharing the joy of riding waves with everyone.</p>

            <p>Our school was founded in 2010 by a group of professional surfers who wanted to share their love of surfing with others. Since then, we've taught thousands of students from beginners to advanced surfers.</p>

            <h2 class="h4 mt-4">Our Mission</h2>
            <p>To provide safe, fun, and professional surfing instruction while promoting ocean awareness and environmental responsibility.</p>

            <h2 class="h4 mt-4">Our Values</h2>
            <ul>
                <li>Safety First</li>
                <li>Quality Instruction</li>
                <li>Environmental Responsibility</li>
                <li>Inclusive Community</li>
            </ul>
        </div>
        <div class="col-lg-6">
            <img src="assets/images/site/about-image.jpg" alt="Surf School Team" class="img-fluid rounded shadow">
        </div>
    </div>

    <div class="row mt-5">
        <h2 class="text-center mb-4">Our Instructors</h2>
        <?php
        include 'config/database.php';
        $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'instructor' LIMIT 3");
        while ($instructor = $stmt->fetch()): ?>
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="<?php echo $instructor['profile_image'] ?? 'assets/images/profiles/default.jpg'; ?>"
                        class="card-img-top rounded-circle mx-auto mt-3"
                        style="width: 150px; height: 150px; object-fit: cover;"
                        alt="<?php echo htmlspecialchars($instructor['username']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($instructor['username']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($instructor['profile_description']); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>