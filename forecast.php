<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'includes/get_weather.php';

// Fetch forecast data from database
$stmt = $pdo->prepare("
    SELECT * FROM surf_conditions 
    WHERE date_time >= CURDATE() 
    AND date_time <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
    ORDER BY date_time ASC
");
$stmt->execute();
$forecast = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">Surf Forecast & Conditions</h1>
            <p class="lead text-muted">
                Get detailed surf conditions and forecasts for the next 14 days
            </p>
            <hr class="my-4">
        </div>
    </div>

    <!-- Current Conditions Summary -->
    <?php if ($forecast && !empty($forecast[0])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg surf-conditions-card position-relative overflow-hidden">
                    <div class="wave-background"></div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h2 class="h3 mb-4 text-white">Current Conditions</h2>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="condition-icon bg-white bg-opacity-10 rounded-circle p-3 me-3">
                                        <i class="fas fa-wave-square fa-2x text-white"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-white-50">Wave Height</p>
                                        <h3 class="mb-0 text-white display-6"><?php echo number_format($forecast[0]['wave_height'], 1); ?>m</h3>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="condition-icon bg-white bg-opacity-10 rounded-circle p-3 me-3">
                                        <i class="fas fa-wind fa-2x text-white"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-white-50">Wind Speed</p>
                                        <h3 class="mb-0 text-white display-6"><?php echo $forecast[0]['wind_speed']; ?> km/h</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-4 mt-md-0">
                                <div class="bg-white bg-opacity-10 rounded p-3">
                                    <p class="h5 mb-3 text-white">Best For Today:</p>
                                    <?php
                                    $conditions = calculateSurfConditions($forecast[0]['wave_height'], $forecast[0]['wind_speed']);
                                    $bestFor = [];
                                    if ($conditions['beginner_status'] === 'good') $bestFor[] = 'Beginners';
                                    if ($conditions['intermediate_status'] === 'good') $bestFor[] = 'Intermediate';
                                    if ($conditions['advanced_status'] === 'good') $bestFor[] = 'Advanced';
                                    ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (!empty($bestFor)): ?>
                                            <?php foreach ($bestFor as $level): ?>
                                                <span class="badge bg-success px-3 py-2"><?php echo $level; ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning px-3 py-2">Check conditions below</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Surf Conditions Rules -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Understanding Our Surf Conditions
                    </h3>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">Wave Height Guidelines</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Wave Height</th>
                                    <th>Beginner</th>
                                    <th>Intermediate</th>
                                    <th>Advanced</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0.5m - 1.0m</td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td><span class="badge bg-warning">Fair</span></td>
                                    <td><span class="badge bg-warning">Fair</span></td>
                                    <td>Small waves, ideal for beginners</td>
                                </tr>
                                <tr>
                                    <td>1.0m - 1.5m</td>
                                    <td><span class="badge bg-warning">Fair</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td>Moderate waves, suitable for intermediate surfers</td>
                                </tr>
                                <tr>
                                    <td>1.5m - 2.0m</td>
                                    <td><span class="badge bg-danger">Poor</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td>Moderate to large waves, not suitable for beginners</td>
                                </tr>
                                <tr>
                                    <td>2.0m - 2.5m</td>
                                    <td><span class="badge bg-dark">Dangerous</span></td>
                                    <td><span class="badge bg-warning">Fair</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td>Large waves, recommended for experienced surfers only</td>
                                </tr>
                                <tr>
                                    <td>2.5m - 3.0m</td>
                                    <td><span class="badge bg-dark">Dangerous</span></td>
                                    <td><span class="badge bg-danger">Poor</span></td>
                                    <td><span class="badge bg-success">Good</span></td>
                                    <td>Very large waves, suitable for advanced surfers only</td>
                                </tr>
                                <tr>
                                    <td>Over 3.0m</td>
                                    <td><span class="badge bg-dark">Dangerous</span></td>
                                    <td><span class="badge bg-dark">Dangerous</span></td>
                                    <td><span class="badge bg-danger">Poor</span></td>
                                    <td>Waves too large and dangerous for most surfers</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mb-3">Wind Speed Impact</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Wind Speed</th>
                                    <th>Effect on Conditions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Under 15 km/h</td>
                                    <td>Minimal impact on surf conditions</td>
                                </tr>
                                <tr>
                                    <td>15-25 km/h</td>
                                    <td>Conditions downgraded one level (e.g., Good → Fair), may cause choppy conditions</td>
                                </tr>
                                <tr>
                                    <td>Over 25 km/h</td>
                                    <td>Conditions become dangerous for all skill levels</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h4 class="mb-3">Status Indicators</h4>
                        <div class="d-flex flex-wrap gap-3">
                            <div><span class="badge bg-success">Good</span> - Ideal conditions</div>
                            <div><span class="badge bg-warning">Fair</span> - Acceptable conditions</div>
                            <div><span class="badge bg-danger">Poor</span> - Not recommended</div>
                            <div><span class="badge bg-dark">Dangerous</span> - Unsafe conditions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 14-Day Forecast -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    14-Day Surf Forecast
                </h2>
                <span class="text-muted">
                    <i class="fas fa-sync-alt me-1"></i>
                    Updated <?php echo date('g:i A, M j, Y'); ?>
                </span>
            </div>
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Wave Height</th>
                                    <th>Wind Speed</th>
                                    <th>Temperature</th>
                                    <th>Beginner</th>
                                    <th>Intermediate</th>
                                    <th>Advanced</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($forecast): ?>
                                    <?php foreach ($forecast as $day): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('D, M j', strtotime($day['date_time'])); ?></strong>
                                                <div class="small text-muted"><?php echo date('Y', strtotime($day['date_time'])); ?></div>
                                            </td>
                                            <td>
                                                <i class="fas fa-wave-square text-primary me-2"></i>
                                                <?php echo number_format($day['wave_height'], 1); ?>m
                                            </td>
                                            <td>
                                                <i class="fas fa-wind text-success me-2"></i>
                                                <?php echo $day['wind_speed']; ?> km/h
                                            </td>
                                            <td>
                                                <i class="fas fa-temperature-high text-danger me-2"></i>
                                                <?php echo $day['temperature']; ?>°C
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getBadgeColor($day['beginner_status']); ?>">
                                                    <?php echo ucfirst($day['beginner_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getBadgeColor($day['intermediate_status']); ?>">
                                                    <?php echo ucfirst($day['intermediate_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getBadgeColor($day['advanced_status']); ?>">
                                                    <?php echo ucfirst($day['advanced_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No forecast data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-center text-muted small">
                        <p><i class="fas fa-info-circle me-1"></i> Forecast data is updated daily. Conditions may change based on weather patterns.</p>
                        <p>For the most accurate surf conditions, we recommend checking the forecast on the day of your planned surf session.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .table th {
        background-color: #f8f9fa;
    }

    .display-4 {
        font-weight: 600;
    }

    .lead {
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }
    }

    .surf-conditions-card {
        background: linear-gradient(135deg, #4a90e2 0%, #2c3e50 100%) !important;
        color: white !important;
    }

    .wave-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ffffff' fill-opacity='1' d='M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
        background-size: cover;
        background-position: center;
        pointer-events: none;
    }

    .condition-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
        background-color: rgba(255, 255, 255, 0.1) !important;
    }

    .condition-icon:hover {
        transform: scale(1.1);
    }

    .display-6 {
        font-size: 1.8rem;
        font-weight: 500;
    }
</style>

<?php
// Helper function for badge colors
function getBadgeColor($status)
{
    switch ($status) {
        case 'good':
            return 'success';
        case 'fair':
            return 'warning';
        case 'poor':
            return 'danger';
        case 'dangerous':
            return 'dark';
        default:
            return 'secondary';
    }
}

include 'includes/footer.php';
?>