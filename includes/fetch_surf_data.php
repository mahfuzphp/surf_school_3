<?php
require_once 'OpenMeteoSurfClient.php';
require_once 'get_weather.php';

function fetchAndStoreSurfData()
{
    global $pdo;

    // Initialize the OpenMeteoSurfClient
    $client = new OpenMeteoSurfClient();

    // Bondi Beach coordinates
    $location = 'Bondi Beach';
    $latitude = -33.8915;
    $longitude = 151.2767;

    try {
        // Get 7-day forecast
        $forecast = $client->getSevenDayForecast($location, $latitude, $longitude);

        // Begin transaction
        $pdo->beginTransaction();

        // Clear existing future forecasts to avoid duplicates
        $stmt = $pdo->prepare("DELETE FROM surf_conditions WHERE date_time >= CURDATE()");
        $stmt->execute();

        // Prepare insert statement
        $stmt = $pdo->prepare("
            INSERT INTO surf_conditions (
                location_name,
                date_time,
                wave_height,
                wind_speed,
                temperature,
                beginner_status,
                intermediate_status,
                advanced_status,
                condition_message
            ) VALUES (
                :location_name,
                :date_time,
                :wave_height,
                :wind_speed,
                :temperature,
                :beginner_status,
                :intermediate_status,
                :advanced_status,
                :condition_message
            )
        ");

        // Insert each day's forecast
        foreach ($forecast['daily'] as $day) {
            // Extract wave height and wind speed values (remove 'm' and 'km/h' from strings)
            $wave_height = floatval(str_replace(' m', '', $day['conditions']['wave_height']));
            $wind_speed = floatval(str_replace(' km/h', '', $day['conditions']['wind_speed']));
            $temperature = floatval(str_replace(' °C', '', $day['conditions']['temperature']));

            // Convert date format
            $date = date('Y-m-d H:i:s', strtotime($day['date']));

            // Determine status based on surf rating

            $status = calculateSurfConditions($wave_height, $wind_speed);

            $stmt->execute([
                'location_name' => $location,
                'date_time' => $date,
                'wave_height' => $wave_height,
                'wind_speed' => $wind_speed,
                'temperature' => $temperature,
                'beginner_status' => $status['beginner_status'],
                'intermediate_status' => $status['intermediate_status'],
                'advanced_status' => $status['advanced_status'],
                'condition_message' => $status['condition_message']
            ]);
        }

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error fetching surf data: " . $e->getMessage());
        return false;
    }
}

function getSurfStatus($rating)
{
    switch ($rating) {
        case 'Epic':
        case 'Excellent':
        case 'Very Good':
            return 'good';
        case 'Good':
        case 'Fair':
            return 'fair';
        case 'Poor':
            return 'poor';
        case 'Not Suitable':
        default:
            return 'dangerous';
    }
}

// Execute the fetch and store operation
fetchAndStoreSurfData();
