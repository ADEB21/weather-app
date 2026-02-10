<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenMeteoClient
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';
    private const TIMEZONE = 'Europe/Paris';
    private const FORECAST_DAYS = 10;
    private const FORECAST_HOURS = 24;

    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getWeather(float $lat, float $lon): array
    {
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,wind_direction_10m,wind_gusts_10m,uv_index',
                'hourly' => 'temperature_2m,weather_code,precipitation_probability',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,wind_direction_10m_dominant,sunrise,sunset,uv_index_max',
                'timezone' => self::TIMEZONE,
                'forecast_days' => self::FORECAST_DAYS,
                'forecast_hours' => self::FORECAST_HOURS,
            ]
        ]);

        return $response->toArray();
    }
}