<?php

namespace App\Controller\Api;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour gérer les villes favorites
 * 
 * Ce contrôleur permet de :
 * - Lister tous les favoris
 * - Ajouter une ville en favori
 * - Vérifier si une ville est en favori
 * - Supprimer un favori
 * 
 * Route de base : /api/favorites
 */
#[Route('/api/favorites', name: 'api_favorites_')]
class FavoriteController extends AbstractController
{
    /**
     * Constructeur du contrôleur
     * 
     * @param FavoriteRepository $favoriteRepository Repository pour accéder à la base de données
     */
    public function __construct(
        private readonly FavoriteRepository $favoriteRepository
    ) {}

    /**
     * Liste tous les favoris
     * 
     * Endpoint : GET /api/favorites
     * 
     * @return JsonResponse Liste de tous les favoris triés par date d'ajout
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupère tous les favoris depuis la base de données, triés par date
        $favorites = $this->favoriteRepository->findAllOrderedByDate();

        // Transforme chaque favori en tableau associatif et retourne le résultat
        // array_map applique la fonction serializeFavorite à chaque élément
        return $this->json([
            'data' => array_map($this->serializeFavorite(...), $favorites)
        ]);
    }

    /**
     * Ajoute une nouvelle ville en favori
     * 
     * Endpoint : POST /api/favorites
     * Body JSON : {"city": "Paris", "country": "FR", "latitude": 48.85, "longitude": 2.35}
     * 
     * @param Request $request La requête HTTP contenant les données de la ville
     * @return JsonResponse Le favori créé ou une erreur si déjà existant
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Décode le JSON envoyé dans le corps de la requête en tableau PHP
        $data = json_decode($request->getContent(), true);

        // Validation : vérifie que latitude et longitude sont présents
        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            return $this->json([
                'error' => 'latitude and longitude are required'
            ], Response::HTTP_BAD_REQUEST); // Code 400
        }

        // Vérifie si cette localisation existe déjà en favori
        $existing = $this->favoriteRepository->findByCoordinates(
            (float) $data['latitude'],
            (float) $data['longitude']
        );

        // Si déjà en favori, retourne une erreur 409 (Conflict) avec les données existantes
        if ($existing) {
            return $this->json([
                'error' => 'This location is already in favorites',
                'data' => $this->serializeFavorite($existing)
            ], Response::HTTP_CONFLICT); // Code 409
        }

        // Crée une nouvelle entité Favorite
        $favorite = new Favorite();
        $favorite->setLatitude((float) $data['latitude']);
        $favorite->setLongitude((float) $data['longitude']);
        // Utilise l'opérateur ?? pour définir null si la clé n'existe pas
        $favorite->setCity($data['city'] ?? null);
        $favorite->setCountry($data['country'] ?? null);

        // Sauvegarde en base de données
        // Le try/catch gère le cas où la contrainte d'unicité serait violée
        try {
            $this->favoriteRepository->save($favorite, true); // true = flush immédiat
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'error' => 'This location is already in favorites'
            ], Response::HTTP_CONFLICT); // Code 409
        }

        // Retourne le favori créé avec le code 201 (Created)
        return $this->json([
            'data' => $this->serializeFavorite($favorite)
        ], Response::HTTP_CREATED); // Code 201
    }

    /**
     * Transforme une entité Favorite en tableau pour l'API JSON
     * 
     * @param Favorite $favorite L'entité à transformer
     * @return array Tableau associatif avec les données du favori
     */
    private function serializeFavorite(Favorite $favorite): array
    {
        return [
            'id' => $favorite->getId(),
            'city' => $favorite->getCity(),
            'country' => $favorite->getCountry(),
            'latitude' => $favorite->getLatitude(),
            'longitude' => $favorite->getLongitude(),
            'addedAt' => $favorite->getAddedAt()->format('c'), // Format ISO 8601
        ];
    }

    /**
     * Vérifie si une localisation est en favori
     * 
     * Endpoint : GET /api/favorites/check?latitude=XX.XX&longitude=YY.YY
     * 
     * @param Request $request La requête contenant les coordonnées
     * @return JsonResponse Indique si la ville est en favori et son ID si oui
     */
    #[Route('/check', name: 'check', methods: ['GET'])]
    public function check(Request $request): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');

        // Validation des paramètres
        if ($latitude === null || $longitude === null) {
            return $this->json([
                'error' => 'latitude and longitude parameters are required'
            ], Response::HTTP_BAD_REQUEST); // Code 400
        }

        // Recherche si un favori existe avec ces coordonnées
        $favorite = $this->favoriteRepository->findByCoordinates(
            (float) $latitude,
            (float) $longitude
        );

        // Retourne true/false et l'ID du favori s'il existe
        // L'opérateur ?-> retourne null si $favorite est null
        return $this->json([
            'isFavorite' => $favorite !== null,
            'favoriteId' => $favorite?->getId()
        ]);
    }

    /**
     * Supprime un favori par son ID
     * 
     * Endpoint : DELETE /api/favorites/{id}
     * 
     * @param int $id L'identifiant du favori à supprimer
     * @return JsonResponse Réponse vide (204) ou erreur 404 si non trouvé
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Recherche le favori par son ID
        $favorite = $this->favoriteRepository->find($id);

        // Si le favori n'existe pas, retourne une erreur 404
        if (!$favorite) {
            return $this->json([
                'error' => 'Favorite not found'
            ], Response::HTTP_NOT_FOUND); // Code 404
        }

        // Supprime le favori de la base de données
        $this->favoriteRepository->remove($favorite, true); // true = flush immédiat

        // Retourne une réponse vide avec le code 204 (No Content)
        return $this->json(null, Response::HTTP_NO_CONTENT); // Code 204
    }
}
