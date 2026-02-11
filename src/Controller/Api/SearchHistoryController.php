<?php

namespace App\Controller\Api;

use App\Entity\SearchHistory;
use App\Repository\SearchHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour gérer l'historique des recherches
 * 
 * Ce contrôleur permet de :
 * - Lister l'historique des recherches
 * - Ajouter une recherche à l'historique
 * - Supprimer une recherche spécifique
 * - Effacer tout l'historique
 * 
 * Route de base : /api/history
 */
#[Route('/api/history', name: 'api_history_')]
class SearchHistoryController extends AbstractController
{
    /**
     * Constructeur du contrôleur
     * 
     * @param SearchHistoryRepository $searchHistoryRepository Repository pour accéder à l'historique
     */
    public function __construct(
        private readonly SearchHistoryRepository $searchHistoryRepository
    ) {}

    /**
     * Liste l'historique des recherches
     * 
     * Endpoint : GET /api/history?limit=10&unique=true
     * 
     * @param Request $request La requête HTTP avec les paramètres optionnels
     * @return JsonResponse Liste des recherches récentes
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Récupère le nombre max de résultats (10 par défaut)
        $limit = $request->query->getInt('limit', 10);
        // Récupère si on veut uniquement les recherches uniques (true par défaut)
        $unique = $request->query->getBoolean('unique', default: true);

        // Selon le paramètre 'unique', récupère soit les recherches uniques, soit toutes
        // L'opérateur ternaire ? : permet de choisir entre deux options
        $searches = $unique
            ? $this->searchHistoryRepository->findUniqueRecentSearches($limit)
            : $this->searchHistoryRepository->findRecentSearches($limit);

        // Transforme chaque recherche en tableau et retourne le résultat
        return $this->json([
            'data' => array_map($this->serializeSearch(...), $searches)
        ]);
    }

    /**
     * Ajoute une recherche à l'historique
     * 
     * Endpoint : POST /api/history
     * Body JSON : {"city": "Paris", "country": "FR", "latitude": 48.85, "longitude": 2.35}
     * 
     * @param Request $request La requête contenant les données de la recherche
     * @return JsonResponse La recherche enregistrée
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Décode le JSON du corps de la requête
        $data = json_decode($request->getContent(), true);

        // Validation : vérifie la présence des coordonnées
        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            return $this->json([
                'error' => 'latitude and longitude are required'
            ], Response::HTTP_BAD_REQUEST); // Code 400
        }

        // Crée une nouvelle entrée d'historique
        $search = new SearchHistory();
        $search->setLatitude((float) $data['latitude']);
        $search->setLongitude((float) $data['longitude']);
        $search->setCity($data['city'] ?? null);
        $search->setCountry($data['country'] ?? null);

        // Sauvegarde en base de données
        $this->searchHistoryRepository->save($search, true);

        // Retourne la recherche créée avec le code 201 (Created)
        return $this->json([
            'data' => $this->serializeSearch($search)
        ], Response::HTTP_CREATED); // Code 201
    }

    /**
     * Transforme une entité SearchHistory en tableau pour l'API JSON
     * 
     * @param SearchHistory $search L'entité à transformer
     * @return array Tableau associatif avec les données de la recherche
     */
    private function serializeSearch(SearchHistory $search): array
    {
        return [
            'id' => $search->getId(),
            'city' => $search->getCity(),
            'country' => $search->getCountry(),
            'latitude' => $search->getLatitude(),
            'longitude' => $search->getLongitude(),
            'searchedAt' => $search->getSearchedAt()->format('c'), // Format ISO 8601
        ];
    }

    /**
     * Efface tout l'historique de recherche
     * 
     * Endpoint : DELETE /api/history/clear
     * 
     * @return JsonResponse Message de confirmation
     */
    #[Route('/clear', name: 'clear', methods: ['DELETE'])]
    public function clear(): JsonResponse
    {
        // Récupère l'EntityManager pour exécuter une requête DQL (Doctrine Query Language)
        $entityManager = $this->searchHistoryRepository->getEntityManager();
        // Supprime toutes les entrées de SearchHistory en une seule requête
        $entityManager->createQuery('DELETE FROM App\\Entity\\SearchHistory')->execute();

        return $this->json([
            'message' => 'All search history cleared'
        ], Response::HTTP_OK); // Code 200
    }

    /**
     * Supprime une recherche spécifique de l'historique
     * 
     * Endpoint : DELETE /api/history/{id}
     * 
     * @param int $id L'identifiant de la recherche à supprimer
     * @return JsonResponse Réponse vide (204) ou erreur 404 si non trouvée
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Recherche l'entrée par son ID
        $search = $this->searchHistoryRepository->find($id);

        // Si non trouvée, retourne une erreur 404
        if (!$search) {
            return $this->json([
                'error' => 'Search history not found'
            ], Response::HTTP_NOT_FOUND); // Code 404
        }

        // Supprime l'entrée de la base de données
        $this->searchHistoryRepository->remove($search, true);

        // Retourne une réponse vide avec le code 204 (No Content)
        return $this->json(null, Response::HTTP_NO_CONTENT); // Code 204
    }
}
