<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service de géocodage (Geocoding)
 * 
 * Le géocodage permet de convertir un nom de ville en coordonnées géographiques.
 * Ce service utilise l'API Open-Meteo Geocoding pour rechercher des villes
 * et obtenir leurs coordonnées (latitude/longitude).
 * 
 * Exemple : "Paris" -> {latitude: 48.8566, longitude: 2.3522}
 */
class GeocodingService
{
    /** @var string URL de l'API de géocodage */
    private const API_URL = 'https://geocoding-api.open-meteo.com/v1/search';
    
    /** @var int Nombre maximum de résultats retournés */
    private const MAX_RESULTS = 10;
    
    /** @var string Langue des résultats */
    private const LANGUAGE = 'fr';

    /**
     * Constructeur du service
     * 
     * @param HttpClientInterface $client Client HTTP injecté par Symfony
     */
    public function __construct(
        private readonly HttpClientInterface $client
    ) {}

    /**
     * Recherche des villes par leur nom
     * 
     * Envoie une requête à l'API de géocodage pour trouver des villes
     * correspondant au nom recherché.
     * 
     * @param string $query Le nom de la ville à rechercher (ex: "Paris")
     * @return array{name: string, country: string, latitude: float, longitude: float}[] Tableau de villes trouvées
     */
    public function searchCity(string $query): array
    {
        // Fait une requête GET vers l'API de géocodage
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [ // Paramètres de recherche
                'name' => $query, // Nom de la ville recherchée
                'count' => self::MAX_RESULTS, // Nombre max de résultats
                'language' => self::LANGUAGE, // Langue (français)
                'format' => 'json', // Format de réponse
            ]
        ]);

        // Convertit la réponse JSON en tableau PHP
        $data = $response->toArray();

        // Vérifie que l'API a retourné des résultats
        if (!isset($data['results']) || !is_array($data['results'])) {
            return []; // Retourne un tableau vide si aucun résultat
        }

        // Transforme chaque résultat de l'API en format standardisé
        // array_map applique la fonction à chaque élément du tableau
        return array_map(function (array $result): array {
            return [
                'name' => $result['name'] ?? '', // Nom de la ville
                'country' => $result['country'] ?? '', // Pays
                'admin1' => $result['admin1'] ?? null, // Région/Département
                'latitude' => (float) ($result['latitude'] ?? 0), // Latitude
                'longitude' => (float) ($result['longitude'] ?? 0), // Longitude
            ];
        }, $data['results']);
    }
}
