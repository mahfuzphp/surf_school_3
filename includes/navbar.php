<?php
// Start with PHP code, no HTML yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Now HTML content can follow -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <img src="/assets/images/logo.webp" alt="Surf School" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/lessons.php">Lessons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contact.php">Contact</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="/admin/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/admin/manage-users.php">Manage Users</a></li>
                                <li><a class="dropdown-item" href="/admin/manage-lessons.php">Manage Lessons</a></li>
                                <li><a class="dropdown-item" href="/admin/manage-bookings.php">Manage Bookings</a></li>
                            <?php elseif ($_SESSION['user_type'] === 'instructor'): ?>
                                <li><a class="dropdown-item" href="/instructor/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/instructor/my-lessons.php">My Lessons</a></li>
                                <li><a class="dropdown-item" href="/instructor/profile.php">Profile</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="/student/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/student/my-bookings.php">My Bookings</a></li>
                                <li><a class="dropdown-item" href="/student/profile.php">Profile</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3 mx-2" href="/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>