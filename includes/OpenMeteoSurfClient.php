<?php

class OpenMeteoSurfClient
{
    /** @var string */
    private $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /** @var string */
    private $marineUrl = 'https://marine-api.open-meteo.com/v1/marine';

    /** @var array */
    private $defaultParams = [
        'timezone' => 'auto',
        'forecast_days' => 14,
        'past_days' => 0,
        'past_hours' => 0,
        'forecast_hours' => 24
    ];

    /**
     * Constants for API parameters
     */
    private const HOURLY_WEATHER_PARAMS = 'temperature_2m,wind_speed_10m,wind_direction_10m,weather_code';
    private const HOURLY_MARINE_PARAMS = 'wave_height,wave_direction,wave_period';
    private const DAILY_WEATHER_PARAMS = 'temperature_2m_max,wind_speed_10m_max,weather_code';
    private const DAILY_MARINE_PARAMS = 'wave_height_max,wave_direction_dominant,wave_period_max';

    /**
     * Surfing condition thresholds for different skill levels
     */
    private const SURF_CONDITIONS = [
        'beginner' => [
            'wave_height_min' => 0.3,
            'wave_height_max' => 1.0,
            'wind_speed_max' => 12,     // km/h
            'optimal_wind_speed' => 8,   // km/h
            'wave_period_min' => 6,      // seconds
            'wave_period_max' => 12,     // seconds
            'wave_height_weight' => 0.4,
            'wind_speed_weight' => 0.3,
            'wave_period_weight' => 0.3
        ],
        'intermediate' => [
            'wave_height_min' => 0.7,
            'wave_height_max' => 2.0,
            'wind_speed_max' => 20,      // km/h
            'optimal_wind_speed' => 15,   // km/h
            'wave_period_min' => 8,       // seconds
            'wave_period_max' => 14,      // seconds
            'wave_height_weight' => 0.35,
            'wind_speed_weight' => 0.35,
            'wave_period_weight' => 0.3
        ],
        'expert' => [
            'wave_height_min' => 1.5,
            'wave_height_max' => 5.0,
            'wind_speed_max' => 25,      // km/h
            'optimal_wind_speed' => 20,   // km/h
            'wave_period_min' => 10,      // seconds
            'wave_period_max' => 18,      // seconds
            'wave_height_weight' => 0.4,
            'wind_speed_weight' => 0.25,
            'wave_period_weight' => 0.35
        ]
    ];

    /**
     * Weather condition mapping
     */
    private const WEATHER_CONDITIONS = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        71 => 'Slight snow fall',
        73 => 'Moderate snow fall',
        75 => 'Heavy snow fall',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail'
    ];

    /**
     * Get current surf report and conditions
     */
    public function getSurfReport($locationName, $latitude, $longitude)
    {
        $weatherParams = array_merge($this->defaultParams, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => self::HOURLY_WEATHER_PARAMS
        ]);

        $marineParams = array_merge($this->defaultParams, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => self::HOURLY_MARINE_PARAMS
        ]);

        try {
            $weatherData = $this->makeApiCall($this->baseUrl, $weatherParams);
            $marineData = $this->makeApiCall($this->marineUrl, $marineParams);

            // Get current values (first hour in the forecast)
            $currentTemp = isset($weatherData['hourly']['temperature_2m'][0]) ? $weatherData['hourly']['temperature_2m'][0] : null;
            $currentWind = isset($weatherData['hourly']['wind_speed_10m'][0]) ? $weatherData['hourly']['wind_speed_10m'][0] : null;
            $currentWindDir = isset($weatherData['hourly']['wind_direction_10m'][0]) ? $weatherData['hourly']['wind_direction_10m'][0] : null;
            $weatherCode = isset($weatherData['hourly']['weather_code'][0]) ? $weatherData['hourly']['weather_code'][0] : 0;

            $waveHeight = isset($marineData['hourly']['wave_height'][0]) ? $marineData['hourly']['wave_height'][0] : null;
            $waveDirection = isset($marineData['hourly']['wave_direction'][0]) ? $marineData['hourly']['wave_direction'][0] : null;
            $wavePeriod = isset($marineData['hourly']['wave_period'][0]) ? $marineData['hourly']['wave_period'][0] : null;

            $surfingAssessment = $this->calculateSurfingConditions(
                $waveHeight ?: 0,
                $currentWind ?: 0,
                $wavePeriod ?: 0,
                $weatherCode,
                $currentWindDir,
                $waveDirection
            );

            $bestLevel = $this->getBestSuitedLevel($surfingAssessment);

            return [
                'location' => [
                    'name' => $locationName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'localtime' => date('Y-m-d H:i'),
                ],
                'current' => [
                    'wave_height' => $waveHeight ? sprintf('%.1f m', $waveHeight) : 'N/A',
                    'wave_period' => $wavePeriod ? sprintf('%.1f s', $wavePeriod) : 'N/A',
                    'wave_direction' => $waveDirection ? sprintf('%d°', $waveDirection) : 'N/A',
                    'wind_speed' => $currentWind ? sprintf('%.1f km/h', $currentWind) : 'N/A',
                    'wind_direction' => $currentWindDir ? sprintf('%d°', $currentWindDir) : 'N/A',
                    'air_temperature' => $currentTemp ? sprintf('%.1f °C', $currentTemp) : 'N/A',
                    'condition' => [
                        'text' => $this->getConditionText($weatherCode),
                    ],
                ],
                'surfing_conditions' => [
                    'rating' => $surfingAssessment[$bestLevel]['rating'],
                    'best_for' => $bestLevel === 'none' ? 'Not Recommended' : ucfirst($bestLevel) . ' Surfers',
                    'details' => $surfingAssessment[$bestLevel]
                ]
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to get surf report: " . $e->getMessage());
        }
    }

    /**
     * Get 7-day forecast
     */
    public function getSevenDayForecast(string $location, float $latitude, float $longitude): array
    {
        $weatherParams = array_merge($this->defaultParams, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'daily' => self::DAILY_WEATHER_PARAMS
        ]);

        $marineParams = array_merge($this->defaultParams, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'daily' => self::DAILY_MARINE_PARAMS
        ]);

        try {
            $weatherData = $this->makeApiCall($this->baseUrl, $weatherParams);
            $marineData = $this->makeApiCall($this->marineUrl, $marineParams);

            $forecast = [
                'location' => [
                    'name' => $location,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ],
                'last_updated' => date('Y-m-d H:i'),
                'daily' => []
            ];

            // Process each day's data
            for ($i = 0; $i < count($weatherData['daily']['time']); $i++) {
                $dayWaveHeight = $marineData['daily']['wave_height_max'][$i] ?? 0;
                $dayWindSpeed = $weatherData['daily']['wind_speed_10m_max'][$i] ?? 0;
                $dayWavePeriod = $marineData['daily']['wave_period_max'][$i] ?? 0;
                $weatherCode = $weatherData['daily']['weather_code'][$i] ?? 0;

                $surfConditions = $this->calculateSurfingConditions(
                    $dayWaveHeight,
                    $dayWindSpeed,
                    $dayWavePeriod,
                    $weatherCode
                );

                $bestLevel = $this->getBestSuitedLevel($surfConditions);

                // Default values for when no suitable level is found
                $surfRating = 'Not Suitable';
                $surfScore = 0;
                $surfReasons = ['Conditions not suitable for surfing'];

                if ($bestLevel !== 'none' && isset($surfConditions[$bestLevel])) {
                    $surfRating = $surfConditions[$bestLevel]['rating'];
                    $surfScore = $surfConditions[$bestLevel]['score'];
                    $surfReasons = $surfConditions[$bestLevel]['reasons'];
                }

                $forecast['daily'][] = [
                    'date' => date('D, M j', strtotime($weatherData['daily']['time'][$i])),
                    'conditions' => [
                        'wave_height' => sprintf('%.1f m', $dayWaveHeight),
                        'wind_speed' => sprintf('%.1f km/h', $dayWindSpeed),
                        'wave_period' => sprintf('%.1f s', $dayWavePeriod),
                        'temperature' => sprintf('%.1f °C', $weatherData['daily']['temperature_2m_max'][$i]),
                        'weather' => $this->getConditionText($weatherCode)
                    ],
                    'surf_rating' => $surfRating,
                    'best_for' => $bestLevel === 'none' ? 'Not Recommended' : ucfirst($bestLevel) . ' Surfers',
                    'details' => [
                        'score' => $surfScore,
                        'reasons' => $surfReasons
                    ]
                ];
            }

            return $forecast;
        } catch (Exception $e) {
            throw new Exception("Failed to get forecast: " . $e->getMessage());
        }
    }

    /**
     * Make API call with error handling
     */
    private function makeApiCall($url, $params)
    {
        $queryString = http_build_query($params);
        $fullUrl = "$url?$queryString";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => ["Accept: application/json"]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP Code $httpCode");
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        if (isset($decodedResponse['error']) && $decodedResponse['error'] === true) {
            throw new Exception("API Error: " . ($decodedResponse['reason'] ?? 'Unknown error'));
        }

        return $decodedResponse;
    }

    /**
     * Calculate surfing condition scores for different skill levels
     */
    private function calculateSurfingConditions(
        float $waveHeight,
        float $windSpeed,
        float $wavePeriod,
        int $weatherCode,
        ?int $windDirection = null,
        ?int $waveDirection = null
    ): array {
        $conditions = [];
        $dangerousWeather = [95, 96, 99]; // Thunderstorm conditions

        foreach (self::SURF_CONDITIONS as $level => $criteria) {
            if (in_array($weatherCode, $dangerousWeather)) {
                $conditions[$level] = [
                    'suitable' => false,
                    'score' => 0,
                    'rating' => 'Dangerous',
                    'reasons' => ['Dangerous weather conditions - thunderstorm']
                ];
                continue;
            }

            // Calculate scores
            $waveHeightScore = $this->calculateWaveHeightScore($waveHeight, $criteria);
            $windScore = $this->calculateWindScore($windSpeed, $criteria);
            $wavePeriodScore = $this->calculateWavePeriodScore($wavePeriod, $criteria);

            // Calculate weighted total score
            $totalScore = round(
                $waveHeightScore * $criteria['wave_height_weight'] +
                    $windScore * $criteria['wind_speed_weight'] +
                    $wavePeriodScore * $criteria['wave_period_weight']
            );

            // Generate reasons array
            $reasons = [];
            if ($waveHeightScore < 50) {
                $reasons[] = $waveHeight < $criteria['wave_height_min'] ?
                    'Waves too small' : 'Waves too big';
            }
            if ($windScore < 50) {
                $reasons[] = 'Wind conditions not optimal';
            }
            if ($wavePeriodScore < 50) {
                $reasons[] = $wavePeriod < $criteria['wave_period_min'] ?
                    'Wave period too short' : 'Wave period too long';
            }

            $conditions[$level] = [
                'suitable' => $totalScore >= 60,
                'score' => $totalScore,
                'rating' => $this->getSurfRating($totalScore),
                'reasons' => empty($reasons) ? ['Good conditions'] : $reasons,
                'details' => [
                    'wave_height_score' => $waveHeightScore,
                    'wind_score' => $windScore,
                    'wave_period_score' => $wavePeriodScore
                ]
            ];
        }

        return $conditions;
    }

    /**
     * Calculate wave height score
     */
    private function calculateWaveHeightScore(float $waveHeight, array $criteria): int
    {
        if ($waveHeight < $criteria['wave_height_min']) {
            return round(($waveHeight / $criteria['wave_height_min']) * 50);
        }
        if ($waveHeight > $criteria['wave_height_max']) {
            return round((1 - (($waveHeight - $criteria['wave_height_max']) / $criteria['wave_height_max'])) * 50);
        }
        // Optimal range score
        $mid = ($criteria['wave_height_max'] + $criteria['wave_height_min']) / 2;
        $deviation = abs($waveHeight - $mid);
        $maxDeviation = ($criteria['wave_height_max'] - $criteria['wave_height_min']) / 2;
        return round(100 - ($deviation / $maxDeviation) * 50);
    }

    /**
     * Calculate wind score
     */
    private function calculateWindScore(float $windSpeed, array $criteria): int
    {
        if ($windSpeed > $criteria['wind_speed_max']) {
            return 0;
        }
        if ($windSpeed <= $criteria['optimal_wind_speed']) {
            return 100;
        }
        // Linear decrease from optimal to max
        $range = $criteria['wind_speed_max'] - $criteria['optimal_wind_speed'];
        $overOptimal = $windSpeed - $criteria['optimal_wind_speed'];
        return round(100 - ($overOptimal / $range) * 100);
    }

    /**
     * Calculate wave period score
     */
    private function calculateWavePeriodScore(float $wavePeriod, array $criteria): int
    {
        if ($wavePeriod < $criteria['wave_period_min']) {
            return round(($wavePeriod / $criteria['wave_period_min']) * 50);
        }
        if ($wavePeriod > $criteria['wave_period_max']) {
            return round((1 - (($wavePeriod - $criteria['wave_period_max']) / $criteria['wave_period_max'])) * 50);
        }
        // Optimal range score
        $mid = ($criteria['wave_period_max'] + $criteria['wave_period_min']) / 2;
        $deviation = abs($wavePeriod - $mid);
        $maxDeviation = ($criteria['wave_period_max'] - $criteria['wave_period_min']) / 2;
        return round(100 - ($deviation / $maxDeviation) * 50);
    }

    /**
     * Get surf rating based on score
     */
    private function getSurfRating(int $score): string
    {
        if ($score >= 90) return 'Epic';
        if ($score >= 80) return 'Excellent';
        if ($score >= 70) return 'Very Good';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Fair';
        if ($score >= 20) return 'Poor';
        return 'Not Suitable';
    }

    /**
     * Convert WMO Weather code to condition text
     */
    private function getConditionText($code)
    {
        return isset(self::WEATHER_CONDITIONS[$code]) ? self::WEATHER_CONDITIONS[$code] : 'Unknown';
    }

    /**
     * Get the best suited surfing level based on conditions
     */
    private function getBestSuitedLevel(array $conditions): string
    {
        // If conditions are dangerous, return 'none'
        foreach ($conditions as $level => $condition) {
            if ($condition['rating'] === 'Dangerous') {
                return 'none';  // No skill level is suitable for dangerous conditions
            }
        }

        $bestLevel = 'none';
        $bestScore = -1;

        foreach ($conditions as $level => $condition) {
            if ($condition['suitable'] && $condition['score'] > $bestScore) {
                $bestScore = $condition['score'];
                $bestLevel = $level;
            }
        }

        return $bestLevel;
    }
}
