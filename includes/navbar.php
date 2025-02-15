<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <img src="/assets/images/logo.webp" alt="Surf School" height="40">
        </a>
        <div class="d-flex align-items-center order-lg-2">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-outline-primary me-2 d-none d-sm-inline-block" href="/login.php">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a class="btn btn-primary d-none d-sm-inline-block" href="/register.php">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            <?php endif; ?>
            <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse order-lg-1" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/lessons.php">
                        <i class="fas fa-book me-1"></i>Lessons
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'forecast-management.php') ? 'active' : ''; ?>"
                        href="/forecast.php">
                        <i class="fas fa-cloud-sun me-1"></i>Forecast
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about.php">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contact.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                </li>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($_SESSION['user_type'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="/admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/admin/manage-users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                            <li><a class="dropdown-item" href="/admin/manage-lessons.php"><i class="fas fa-chalkboard-teacher me-2"></i>Manage Lessons</a></li>
                            <li><a class="dropdown-item" href="/admin/manage-bookings.php"><i class="fas fa-calendar-check me-2"></i>Manage Bookings</a></li>
                            <li><a class="dropdown-item" href="/admin/forecast-management.php"><i class="fas fa-cloud-sun me-2"></i>Manage Forecast</a></li>
                        <?php elseif ($_SESSION['user_type'] === 'instructor'): ?>
                            <li><a class="dropdown-item" href="/instructor/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/instructor/my-lessons.php"><i class="fas fa-book me-2"></i>My Lessons</a></li>
                            <li><a class="dropdown-item" href="/instructor/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="/student/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/student/my-bookings.php"><i class="fas fa-calendar me-2"></i>My Bookings</a></li>
                            <li><a class="dropdown-item" href="/student/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Mobile Login/Register buttons -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="d-sm-none mt-3">
                    <a class="btn btn-outline-primary w-100 mb-2" href="/login.php">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a class="btn btn-primary w-100" href="/register.php">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    .navbar {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    .nav-link {
        position: relative;
        padding: 0.5rem 1rem;
        transition: color 0.3s ease;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background: var(--bs-primary);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
    }

    .dropdown-item {
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        transform: translateX(5px);
    }

    .btn {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: white;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 1rem;
        }

        .nav-link::after {
            display: none;
        }

        .dropdown-menu {
            box-shadow: none;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
    }

    /* Animation for dropdown */
    .dropdown-menu {
        animation: dropdownAnimation 0.3s ease forwards;
        transform-origin: top;
    }

    @keyframes dropdownAnimation {
        from {
            opacity: 0;
            transform: scaleY(0.7);
        }

        to {
            opacity: 1;
            transform: scaleY(1);
        }
    }
</style>