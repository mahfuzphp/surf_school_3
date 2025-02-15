<?php
require_once 'config/database.php';
require_once 'includes/get_weather.php';

const MIN_SURFABLE_WAVE_HEIGHT = 0.5;
const MAX_SAFE_WIND_SPEED = 25;

// Test data
$testData = [
    [
        'wave_height' => 0.5,
        'wind_speed' => 10,
        'temperature' => 24
    ],
    [
        'wave_height' => 1.2,
        'wind_speed' => 15,
        'temperature' => 22
    ],
    [
        'wave_height' => 2.0,
        'wind_speed' => 25,
        'temperature' => 26
    ],
    [
        'wave_height' => 1.5,
        'wind_speed' => 10,
        'temperature' => 24
    ],
    [
        'wave_height' => 3,
        'wind_speed' => 30,
        'temperature' => 22
    ],
    [
        'wave_height' => 2.0,
        'wind_speed' => 20,
        'temperature' => 26
    ]
];

foreach ($testData as $data) {
    $conditions = calculateSurfConditions($data['wave_height'], $data['wind_speed']);

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
            'Bondi Beach',
            NOW(),
            :wave_height,
            :wind_speed,
            :temperature,
            :beginner_status,
            :intermediate_status,
            :advanced_status,
            :condition_message
        )
    ");

    $stmt->execute([
        'wave_height' => $data['wave_height'],
        'wind_speed' => $data['wind_speed'],
        'temperature' => $data['temperature'],
        'beginner_status' => $conditions['beginner_status'],
        'intermediate_status' => $conditions['intermediate_status'],
        'advanced_status' => $conditions['advanced_status'],
        'condition_message' => $conditions['condition_message']
    ]);

    echo "Added test data:\n";
    echo "Wave Height: {$data['wave_height']}m\n";
    echo "Wind Speed: {$data['wind_speed']}km/h\n";
    echo "Temperature: {$data['temperature']}°C\n";
    echo "Condition Message: {$conditions['condition_message']}\n\n";
}
