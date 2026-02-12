<?php

namespace App\Controller\Api;

use App\Service\OpenMeteoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour gérer les requêtes météo
 * 
 * Ce contrôleur expose une API REST pour récupérer les données météorologiques
 * d'une localisation donnée (latitude/longitude).
 * 
 * Route de base : /api/weather
 */
#[Route('/api/weather', name: 'api_weather')]
class WeatherController extends AbstractController
{
    /**
     * Récupère les données météo pour une position géographique
     * 
     * Endpoint : GET /api/weather?latitude=XX.XX&longitude=YY.YY
     * 
     * @param Request $request La requête HTTP contenant les paramètres
     * @param OpenMeteoClient $client Service pour appeler l'API Open-Meteo
     * @return JsonResponse Les données météo au format JSON
     */
    #[Route('', methods: ['GET'])]
    public function __invoke(Request $request, OpenMeteoClient $client): JsonResponse
    {
        // Récupère les paramètres latitude et longitude depuis l'URL
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');

        // Validation : vérifie que les deux paramètres sont présents
        if ($latitude === null || $longitude === null) {
            return $this->json([
                'error' => 'latitude and longitude parameters are required'
            ], Response::HTTP_BAD_REQUEST); // Code 400 : Mauvaise requête
        }

        // Appelle le service OpenMeteoClient pour récupérer les données météo
        // Convertit les coordonnées en float (nombre décimal)
        $data = $client->getWeather((float) $latitude, (float) $longitude);

        // Retourne les données au format JSON (code 200 : Succès)
        return $this->json($data);
    }
}
