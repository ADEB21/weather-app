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

#[Route('/api/favorites', name: 'api_favorites_')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private readonly FavoriteRepository $favoriteRepository
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $favorites = $this->favoriteRepository->findAllOrderedByDate();

        return $this->json([
            'data' => array_map(fn(Favorite $favorite) => [
                'id' => $favorite->getId(),
                'city' => $favorite->getCity(),
                'country' => $favorite->getCountry(),
                'latitude' => $favorite->getLatitude(),
                'longitude' => $favorite->getLongitude(),
                'addedAt' => $favorite->getAddedAt()->format('c'),
            ], $favorites)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            return $this->json([
                'error' => 'latitude and longitude are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->favoriteRepository->findByCoordinates(
            (float) $data['latitude'],
            (float) $data['longitude']
        );

        if ($existing) {
            return $this->json([
                'error' => 'This location is already in favorites',
                'data' => [
                    'id' => $existing->getId(),
                    'city' => $existing->getCity(),
                    'country' => $existing->getCountry(),
                    'latitude' => $existing->getLatitude(),
                    'longitude' => $existing->getLongitude(),
                    'addedAt' => $existing->getAddedAt()->format('c'),
                ]
            ], Response::HTTP_CONFLICT);
        }

        $favorite = new Favorite();
        $favorite->setLatitude((float) $data['latitude']);
        $favorite->setLongitude((float) $data['longitude']);
        $favorite->setCity($data['city'] ?? null);
        $favorite->setCountry($data['country'] ?? null);

        try {
            $this->favoriteRepository->save($favorite, true);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'error' => 'This location is already in favorites'
            ], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'data' => [
                'id' => $favorite->getId(),
                'city' => $favorite->getCity(),
                'country' => $favorite->getCountry(),
                'latitude' => $favorite->getLatitude(),
                'longitude' => $favorite->getLongitude(),
                'addedAt' => $favorite->getAddedAt()->format('c'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/check', name: 'check', methods: ['GET'])]
    public function check(Request $request): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');

        if ($latitude === null || $longitude === null) {
            return $this->json([
                'error' => 'latitude and longitude parameters are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $favorite = $this->favoriteRepository->findByCoordinates(
            (float) $latitude,
            (float) $longitude
        );

        return $this->json([
            'isFavorite' => $favorite !== null,
            'favoriteId' => $favorite?->getId()
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $favorite = $this->favoriteRepository->find($id);

        if (!$favorite) {
            return $this->json([
                'error' => 'Favorite not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->favoriteRepository->remove($favorite, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
