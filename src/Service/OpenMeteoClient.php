<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenMeteoClient
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getWeather(float $lat, float $lon): array
    {
        $response = $this->client->request('GET', 'https://api.open-meteo.com/v1/forecast', [
            'query' => [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,wind_direction_10m',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,wind_direction_10m_dominant',
                'timezone' => 'Europe/Paris',
                'forecast_days' => 7,
            ]
        ]);

        return $response->toArray();
    }
}