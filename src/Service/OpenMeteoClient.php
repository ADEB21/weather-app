<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service pour communiquer avec l'API Open-Meteo
 * 
 * Ce service est responsable de récupérer les données météorologiques
 * depuis l'API gratuite Open-Meteo (https://open-meteo.com)
 * 
 * Il retourne les données actuelles, horaires et quotidiennes pour une localisation.
 */
class OpenMeteoClient
{
    /** @var string URL de l'API Open-Meteo */
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';
    
    /** @var string Fuseau horaire pour les données */
    private const TIMEZONE = 'Europe/Paris';
    
    /** @var int Nombre de jours de prévisions */
    private const FORECAST_DAYS = 10;
    
    /** @var int Nombre d'heures de prévisions */
    private const FORECAST_HOURS = 24;

    /**
     * Constructeur du service
     * 
     * @param HttpClientInterface $client Client HTTP injecté par Symfony pour faire des requêtes
     */
    public function __construct(
        private HttpClientInterface $client
    ) {}

    /**
     * Récupère les données météo pour une localisation
     * 
     * Fait une requête GET à l'API Open-Meteo avec les paramètres suivants :
     * - current : Données météo actuelles (température, humidité, vent, etc.)
     * - hourly : Prévisions heure par heure pour les 24 prochaines heures
     * - daily : Prévisions quotidiennes pour les 10 prochains jours
     * 
     * @param float $lat Latitude de la localisation
     * @param float $lon Longitude de la localisation
     * @return array Tableau contenant toutes les données météo
     */
    public function getWeather(float $lat, float $lon): array
    {
        // Fait une requête HTTP GET vers l'API
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [ // Paramètres de l'URL (?latitude=XX&longitude=YY...)
                'latitude' => $lat,
                'longitude' => $lon,
                // Données actuelles demandées
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,wind_direction_10m,wind_gusts_10m,uv_index',
                // Données horaires demandées
                'hourly' => 'temperature_2m,weather_code,precipitation_probability',
                // Données quotidiennes demandées
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,wind_direction_10m_dominant,sunrise,sunset,uv_index_max',
                'timezone' => self::TIMEZONE,
                'forecast_days' => self::FORECAST_DAYS,
                'forecast_hours' => self::FORECAST_HOURS,
            ]
        ]);

        // Convertit la réponse JSON en tableau PHP
        return $response->toArray();
    }
}