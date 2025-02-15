<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/OpenMeteoClient.php';
require_once BASE_PATH . '/includes/OpenMeteoSurfClient.php';



function getWeatherDisplay($weather)
{
    if (!$weather) return '';

    ob_start();
?>
    <div class="weather-widget mb-4">
        <div class="card bg-gradient-primary border-0 shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="text-white mb-1">Surf Conditions</h4>
                        <p class="text-white-50 mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($weather['location']['name']); ?>
                        </p>
                    </div>
                    <div class="weather-icon">
                        <i class="fas fa-water fa-2x text-white-50"></i>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="weather-info-card">
                            <div class="weather-info-icon">
                                <i class="fas fa-temperature-high"></i>
                            </div>
                            <div class="weather-info-content">
                                <h3 class="mb-0"><?php echo htmlspecialchars($weather['current']['air_temperature']); ?></h3>
                                <p class="text-muted mb-0">Temperature</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="weather-info-card">
                            <div class="weather-info-icon">
                                <i class="fas fa-wave-square"></i>
                            </div>
                            <div class="weather-info-content">
                                <h3 class="mb-0"><?php echo htmlspecialchars($weather['current']['wave_height']); ?></h3>
                                <p class="text-muted mb-0">Wave Height</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="weather-info-card">
                            <div class="weather-info-icon">
                                <i class="fas fa-wind"></i>
                            </div>
                            <div class="weather-info-content">
                                <h3 class="mb-0"><?php echo htmlspecialchars($weather['current']['wind_speed']); ?></h3>
                                <p class="text-muted mb-0">Wind Speed</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="weather-info-card">
                            <div class="weather-info-icon">
                                <i class="fas fa-cloud-sun"></i>
                            </div>
                            <div class="weather-info-content">
                                <h3 class="mb-0"><?php echo htmlspecialchars($weather['current']['condition']['text']); ?></h3>
                                <p class="text-muted mb-0">Conditions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <small class="text-white-50">
                        <i class="far fa-clock me-1"></i>
                        Last updated: <?php echo htmlspecialchars($weather['location']['localtime']); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <style>
        .weather-widget .card {
            background: linear-gradient(45deg, #0a4c95 0%, #00a5b9 100%);
            border-radius: 15px;
        }

        .weather-info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            transition: transform 0.2s;
        }

        .weather-info-card:hover {
            transform: translateY(-5px);
        }

        .weather-info-icon {
            color: #0a4c95;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .weather-info-content h3 {
            color: #2c3e50;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .weather-info-content p {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #0a4c95 0%, #00a5b9 100%);
        }

        @media (max-width: 768px) {
            .weather-info-card {
                margin-bottom: 15px;
            }

            .row.g-4>div {
                margin-bottom: 15px;
            }
        }
    </style>
<?php
    return ob_get_clean();
}

function fetchWeatherData($location = 'Bondi Beach')
{
    // First try to get cached data
    $cachedData = getWeatherFromCache($location);
    if ($cachedData) {
        return $cachedData;
    }
    // Bondi Beach, Australia
    $latitude = -33.8915;
    $longitude = 151.2767;
    // If no cached data, fetch from API

    // $client = new OpenMeteoClient();
    // $clientSurf = new OpenMeteoSurfClient();
    // $weatherData = $client->getCurrentConditions($location, $latitude, $longitude);


    $client = new OpenMeteoSurfClient();
    $surfReport = $client->getSurfReport('Bondi Beach', -33.8915, 151.2767);


    // Save the new data to cache
    saveWeatherToCache($location, $surfReport);


    return $surfReport;
}

function getWeatherFromCache($location)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT weather_data 
            FROM weather_cache 
            WHERE location = :location 
            AND last_updated > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute(['location' => 'weather_' . $location]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return json_decode($row['weather_data'], true);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }

    return null;
}

function saveWeatherToCache($location, $data)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO weather_cache (location, weather_data) 
            VALUES (:location, :weather_data)
            ON DUPLICATE KEY UPDATE 
            weather_data = VALUES(weather_data),
            last_updated = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            'location' => 'weather_' . $location,
            'weather_data' => json_encode($data)
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}


function getMonthlyForecastDisplay($forecast)
{
    // Add caching for the HTML display
    $cache_key = 'forecast_display_' . md5(json_encode($forecast));
    $cached_html = getDisplayFromCache($cache_key);

    if ($cached_html !== false) {
        return $cached_html;
    }

    if (!$forecast) return '';
    ob_start();
?>
    <div class="forecast-widget mb-4">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Surf Conditions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Wave Height</th>
                                <th>Wind Speed</th>
                                <th>Temperature</th>
                                <th>Conditions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forecast['daily'] as $day): ?>
                                <tr>
                                    <td><?php echo date('D, M j', strtotime($day['date'])); ?></td>
                                    <td>
                                        <i class="fas fa-wave-square text-primary me-2"></i>
                                        <?php echo $day['wave_height']; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-wind text-info me-2"></i>
                                        <?php echo $day['wind_speed']; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-temperature-high text-danger me-2"></i>
                                        <?php echo $day['temperature']; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo $day['condition']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i>
                        Last updated: <?php echo $forecast['last_updated']; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <style>
        .forecast-widget .table th {
            font-weight: 600;
            border-top: none;
        }

        .forecast-widget .table td {
            vertical-align: middle;
        }

        .forecast-widget .badge {
            font-weight: 500;
            padding: 0.5em 1em;
        }

        @media (max-width: 768px) {
            .forecast-widget .table {
                font-size: 0.875rem;
            }
        }
    </style>
<?php
    $html = ob_get_clean();

    // Cache the generated HTML
    saveDisplayToCache($cache_key, $html);

    return $html;
}

/**
 * Get cached display HTML
 */
function getDisplayFromCache($cache_key)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT weather_data as display_html
            FROM weather_cache 
            WHERE location = ? 
            AND last_updated > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute(['display_' . $cache_key]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['display_html'];
        }
    } catch (PDOException $e) {
        error_log("Display cache fetch error: " . $e->getMessage());
    }

    return false;
}

/**
 * Save display HTML to cache
 */
function saveDisplayToCache($cache_key, $html)
{
    global $pdo;

    try {
        // First, clean old cache entries
        $stmt = $pdo->prepare("
            DELETE FROM weather_cache 
            WHERE last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY)
            AND location LIKE 'display_%'
        ");
        $stmt->execute();

        // Then insert new cache entry with 'display_' prefix
        $stmt = $pdo->prepare("
            INSERT INTO weather_cache (location, weather_data, last_updated) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            weather_data = VALUES(weather_data),
            last_updated = NOW()
        ");        //dd($stmt);
        $stmt->execute(['display_' . $cache_key, $html]);
    } catch (PDOException $e) {
        error_log("Display cache save error: " . $e->getMessage());
    }
}

function fetchMonthlyForecast($location = 'Bondi Beach')
{


    global $pdo;
    // Create a cache key that includes the date
    $cache_key = 'forecast_' . $location . '_' . date('Y-m-d');

    // Try to get cached forecast first
    $cachedData = getForecastFromCache($cache_key);
    if ($cachedData) {
        return $cachedData;
    }

    // If no cached data, fetch from API
    $latitude = -33.8915; // Bondi Beach, Australia
    $longitude = 151.2767;

    $client = new OpenMeteoSurfClient();
    $forecast = $client->getSevenDayForecast($location, $latitude, $longitude);



    // Save to cache with the date-specific key
    try {
        $stmt = $pdo->prepare("
            INSERT INTO weather_cache (location, weather_data, last_updated) 
            VALUES (:location, :weather_data, NOW())
            ON DUPLICATE KEY UPDATE 
            weather_data = VALUES(weather_data),
            last_updated = NOW()
        ");

        $stmt->execute([
            'location' => $cache_key,
            'weather_data' => json_encode($forecast)
        ]);
    } catch (PDOException $e) {
        error_log("Cache save error: " . $e->getMessage());
    }

    return $forecast;
}

function getForecastFromCache($cache_key)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT weather_data 
            FROM weather_cache 
            WHERE location = :location 
            AND last_updated > DATE_SUB(NOW(), INTERVAL 6 HOUR)
            AND weather_data LIKE '%daily%'
        ");
        $stmt->execute(['location' => $cache_key]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return json_decode($row['weather_data'], true);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }

    return null;
}
function calculateSurfConditions($waveHeight, $windSpeed)
{
    // Default statuses and message
    $beginnerStatus = 'poor';
    $intermediateStatus = 'poor';
    $advancedStatus = 'poor';
    $conditionMessage = '';

    // Determine status based on wave height
    if ($waveHeight >= 0.5 && $waveHeight <= 1.0) {
        $beginnerStatus = 'good';
        $intermediateStatus = 'fair';
        $advancedStatus = 'fair';
        $conditionMessage = "Small waves, ideal for beginners.";
    } elseif ($waveHeight > 1.0 && $waveHeight < 1.5) {  // Changed upper bound to 1.5
        $beginnerStatus = 'fair';
        $intermediateStatus = 'good';
        $advancedStatus = 'good';
        $conditionMessage = "Moderate waves, suitable for intermediate surfers.";
    } elseif ($waveHeight >= 1.5 && $waveHeight < 2.0) {  // New condition for 1.5-2.0
        $beginnerStatus = 'poor';      // Beginners shouldn't surf
        $intermediateStatus = 'good';
        $advancedStatus = 'good';
        $conditionMessage = "Moderate to large waves, not suitable for beginners.";
    } elseif ($waveHeight >= 2.0 && $waveHeight <= 2.5) {  // Changed range for 2.0-2.5
        $beginnerStatus = 'dangerous';  // Changed to dangerous for beginners
        $intermediateStatus = 'fair';
        $advancedStatus = 'good';
        $conditionMessage = "Large waves, recommended for experienced surfers only. Dangerous for beginners.";
    } elseif ($waveHeight > 2.5 && $waveHeight <= 3.0) {  // New range for 2.5-3.0
        $beginnerStatus = 'dangerous';
        $intermediateStatus = 'poor';   // Changed to poor for intermediate
        $advancedStatus = 'good';
        $conditionMessage = "Very large waves, suitable for advanced surfers only.";
    } elseif ($waveHeight > 3.0) {
        $beginnerStatus = 'dangerous';
        $intermediateStatus = 'dangerous';
        $advancedStatus = 'poor';
        $conditionMessage = "Waves are too large and dangerous for most surfers.";
    } else {
        $conditionMessage = "Waves are too small for surfing.";
    }

    // Adjust statuses based on wind speed
    if ($windSpeed >= 25) {
        $beginnerStatus = 'dangerous';
        $intermediateStatus = 'dangerous';
        $advancedStatus = 'dangerous';
        $conditionMessage = "Strong winds make conditions unsafe for surfing.";
    } elseif ($windSpeed >= 15 && $windSpeed < 25) {
        if ($beginnerStatus != 'dangerous') {
            $beginnerStatus = $beginnerStatus == 'good' ? 'fair' : $beginnerStatus;
            $intermediateStatus = $intermediateStatus == 'good' ? 'fair' : $intermediateStatus;
            $advancedStatus = $advancedStatus == 'good' ? 'fair' : $advancedStatus;
            $conditionMessage .= " Moderate winds may cause choppy conditions.";
        }
    }

    return [
        'beginner_status' => $beginnerStatus,
        'intermediate_status' => $intermediateStatus,
        'advanced_status' => $advancedStatus,
        'condition_message' => $conditionMessage
    ];
}
