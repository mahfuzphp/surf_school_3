<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config/database.php';

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

    // If no cached data, fetch from API
    $latitude = -33.8915; // Bondi Beach, Australia
    $longitude = 151.2767;
    $url = "https://marine-api.open-meteo.com/v1/marine?latitude={$latitude}&longitude={$longitude}&hourly=wave_height,wind_speed,air_temperature";

    // Initialize a cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        curl_close($ch);
        return getSampleWeatherData(); // Return sample data if API call fails
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if the API returned valid data
    if (!isset($data['hourly'])) {
        $data = getSampleWeatherData();
    } else {
        $data = [
            'location' => [
                'name' => 'Bondi Beach',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'localtime' => date('Y-m-d H:i'),
            ],
            'current' => [
                'wave_height' => $data['hourly']['wave_height'][0] . " m",
                'wind_speed' => $data['hourly']['wind_speed'][0] . " km/h",
                'air_temperature' => $data['hourly']['air_temperature'][0] . " °C",
                'condition' => [
                    'text' => 'Data from Open-Meteo',
                ],
            ],
        ];
    }

    // Save the new data to cache
    saveWeatherToCache($location, $data);

    return $data;
}

function getWeatherFromCache($location)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT weather_data FROM weather_cache 
                              WHERE location = :location 
                              AND last_updated > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute(['location' => $location]);

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
        $stmt = $pdo->prepare("INSERT INTO weather_cache (location, weather_data) 
                              VALUES (:location, :weather_data)
                              ON DUPLICATE KEY UPDATE 
                              weather_data = VALUES(weather_data),
                              last_updated = CURRENT_TIMESTAMP");

        $stmt->execute([
            'location' => $location,
            'weather_data' => json_encode($data)
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

function getSampleWeatherData()
{
    return [
        'location' => [
            'name' => 'Bondi Beach',
            'latitude' => -33.8915,
            'longitude' => 151.2767,
            'localtime' => date('Y-m-d H:i'),
        ],
        'current' => [
            'wave_height' => '1.2 m',
            'wind_speed' => '15 km/h',
            'air_temperature' => '26 °C',
            'condition' => [
                'text' => 'Sunny',
            ],
        ],
    ];
}
