<?php

namespace App\Controller\Api;

use App\Service\GeocodingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la recherche de villes (Geocoding)
 * 
 * Ce contrôleur permet de rechercher des villes par leur nom et d'obtenir
 * leurs coordonnées géographiques (latitude/longitude).
 * 
 * Route de base : /api/geocoding
 */
#[Route('/api/geocoding', name: 'api_geocoding_')]
class GeocodingController extends AbstractController
{
    /**
     * Constructeur du contrôleur
     * 
     * @param GeocodingService $geocodingService Service injecté automatiquement par Symfony
     *                                           pour effectuer les recherches de villes
     */
    public function __construct(
        private readonly GeocodingService $geocodingService
    ) {}

    /**
     * Recherche une ville par son nom
     * 
     * Endpoint : GET /api/geocoding/search?q=NomDeLaVille
     * 
     * @param Request $request La requête HTTP contenant le paramètre de recherche
     * @return JsonResponse Liste des villes trouvées avec leurs coordonnées
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        // Récupère le paramètre 'q' (query) depuis l'URL, vide par défaut
        $query = $request->query->get('q', '');

        // Validation : vérifie que le paramètre n'est pas vide (après suppression des espaces)
        if (empty(trim($query))) {
            return $this->json([
                'error' => 'Query parameter "q" is required'
            ], Response::HTTP_BAD_REQUEST); // Code 400 : Mauvaise requête
        }

        // Appelle le service pour rechercher la ville via l'API Open-Meteo Geocoding
        $results = $this->geocodingService->searchCity($query);

        // Retourne les résultats au format JSON
        // Exemple : [{"name": "Paris", "country": "France", "latitude": 48.85, "longitude": 2.35}]
        return $this->json([
            'data' => $results
        ]);
    }
}
