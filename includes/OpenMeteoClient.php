<?php

class OpenMeteoClient
{
    /** @var string */
    private $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /** @var string */
    private $marineUrl = 'https://marine-api.open-meteo.com/v1/marine';

    /** @var array */
    private $defaultParams = [
        'timezone' => 'auto',
        'forecast_days' => 7
    ];

    /** @var array */
    private const WEATHER_VARIABLES = [
        'temperature_2m',
        'relative_humidity_2m',
        'apparent_temperature',
        'precipitation_probability',
        'precipitation',
        'wind_speed_10m',
        'wind_direction_10m',
        'wind_gusts_10m',
        'weather_code'  // WMO Weather interpretation codes
    ];

    /** @var array */
    private const MARINE_VARIABLES = [
        'wave_height',
        'wave_direction',
        'wave_period',
        'swell_wave_height',
        'swell_wave_direction',
        'swell_wave_period'
    ];

    /**
     * Get weather forecast for specific coordinates
     *
     * @param float $latitude Latitude between -90 and 90
     * @param float $longitude Longitude between -180 and 180
     * @param array $hourlyVariables Array of variables to fetch (from WEATHER_VARIABLES)
     * @param array $additionalParams Optional additional parameters
     * @return array Decoded API response
     * @throws Exception If API call fails or invalid parameters
     */
    public function getWeatherForecast($latitude, $longitude, $hourlyVariables = ['temperature_2m'], $additionalParams = [])
    {
        $this->validateCoordinates($latitude, $longitude);
        $this->validateHourlyVariables($hourlyVariables, self::WEATHER_VARIABLES);

        $params = array_merge(
            $this->defaultParams,
            $additionalParams,
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => implode(',', $hourlyVariables)
            ]
        );

        return $this->makeApiCall($this->baseUrl, $params);
    }

    /**
     * Get marine forecast for specific coordinates
     *
     * @param float $latitude Latitude between -90 and 90
     * @param float $longitude Longitude between -180 and 180
     * @param array $hourlyVariables Array of variables to fetch (from MARINE_VARIABLES)
     * @param array $additionalParams Optional additional parameters
     * @return array Decoded API response
     * @throws Exception If API call fails or invalid parameters
     */
    public function getMarineForecast($latitude, $longitude, $hourlyVariables = ['wave_height'], $additionalParams = [])
    {
        $this->validateCoordinates($latitude, $longitude);
        $this->validateHourlyVariables($hourlyVariables, self::MARINE_VARIABLES);

        $params = array_merge(
            $this->defaultParams,
            $additionalParams,
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => implode(',', $hourlyVariables)
            ]
        );

        return $this->makeApiCall($this->marineUrl, $params);
    }

    /**
     * Make the actual API call
     *
     * @param string $url Base URL for the API endpoint
     * @param array $params Query parameters
     * @return array Decoded response
     * @throws Exception If API call fails
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
     * Validate coordinates are within acceptable ranges
     *
     * @param float $latitude
     * @param float $longitude
     * @throws Exception If coordinates are invalid
     */
    private function validateCoordinates($latitude, $longitude)
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new Exception("Invalid latitude: must be between -90 and 90");
        }
        if ($longitude < -180 || $longitude > 180) {
            throw new Exception("Invalid longitude: must be between -180 and 180");
        }
    }

    /**
     * Validate hourly variables are in the allowed list
     *
     * @param array $variables Variables to validate
     * @param array $allowedVariables List of allowed variables
     * @throws Exception If any variable is not in the allowed list
     */
    private function validateHourlyVariables($variables, $allowedVariables)
    {
        $invalidVariables = array_diff($variables, $allowedVariables);
        if (!empty($invalidVariables)) {
            throw new Exception("Invalid variables: " . implode(', ', $invalidVariables));
        }
    }

    /**
     * Convert WMO Weather code to condition text
     * 
     * @param int $code WMO Weather interpretation code
     * @return string Weather condition text
     */
    private function getConditionText($code)
    {
        $conditions = [
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
            99 => 'Thunderstorm with heavy hail',
        ];

        return isset($conditions[$code]) ? $conditions[$code] : 'Unknown';
    }

    /**
     * Get formatted current weather and marine conditions
     *
     * @param string $locationName Name of the location
     * @param float $latitude Latitude between -90 and 90
     * @param float $longitude Longitude between -180 and 180
     * @return array Formatted weather and marine data
     * @throws Exception If API calls fail
     */
    public function getCurrentConditions($locationName, $latitude, $longitude)
    {
        // Get weather data
        $weatherData = $this->getWeatherForecast(
            $latitude,
            $longitude,
            ['temperature_2m', 'wind_speed_10m', 'weather_code']
        );

        // Get marine data
        $marineData = $this->getMarineForecast(
            $latitude,
            $longitude,
            ['wave_height']
        );

        // Get current values (first hour in the forecast)
        $currentTemp = isset($weatherData['hourly']['temperature_2m'][0]) ? $weatherData['hourly']['temperature_2m'][0] : null;
        $currentWind = isset($weatherData['hourly']['wind_speed_10m'][0]) ? $weatherData['hourly']['wind_speed_10m'][0] : null;
        $currentWaveHeight = isset($marineData['hourly']['wave_height'][0]) ? $marineData['hourly']['wave_height'][0] : null;
        $weatherCode = isset($weatherData['hourly']['weather_code'][0]) ? $weatherData['hourly']['weather_code'][0] : null;

        return [
            'location' => [
                'name' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'localtime' => date('Y-m-d H:i'),
            ],
            'current' => [
                'wave_height' => $currentWaveHeight ? sprintf('%.1f m', $currentWaveHeight) : 'N/A',
                'wind_speed' => $currentWind ? sprintf('%.1f km/h', $currentWind) : 'N/A',
                'air_temperature' => $currentTemp ? sprintf('%.1f °C', $currentTemp) : 'N/A',
                'condition' => [
                    'text' => $weatherCode !== null ? $this->getConditionText($weatherCode) : 'Unknown',
                ],
            ],
        ];
    }
}
