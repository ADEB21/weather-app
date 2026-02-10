<?php

namespace App\Controller\Api;

use App\Entity\SearchHistory;
use App\Repository\SearchHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/history', name: 'api_history_')]
class SearchHistoryController extends AbstractController
{
    public function __construct(
        private readonly SearchHistoryRepository $searchHistoryRepository
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $unique = $request->query->getBoolean('unique', default: true);

        $searches = $unique
            ? $this->searchHistoryRepository->findUniqueRecentSearches($limit)
            : $this->searchHistoryRepository->findRecentSearches($limit);

        return $this->json([
            'data' => array_map($this->serializeSearch(...), $searches)
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

        $search = new SearchHistory();
        $search->setLatitude((float) $data['latitude']);
        $search->setLongitude((float) $data['longitude']);
        $search->setCity($data['city'] ?? null);
        $search->setCountry($data['country'] ?? null);

        $this->searchHistoryRepository->save($search, true);

        return $this->json([
            'data' => $this->serializeSearch($search)
        ], Response::HTTP_CREATED);
    }

    private function serializeSearch(SearchHistory $search): array
    {
        return [
            'id' => $search->getId(),
            'city' => $search->getCity(),
            'country' => $search->getCountry(),
            'latitude' => $search->getLatitude(),
            'longitude' => $search->getLongitude(),
            'searchedAt' => $search->getSearchedAt()->format('c'),
        ];
    }

    #[Route('/clear', name: 'clear', methods: ['DELETE'])]
    public function clear(): JsonResponse
    {
        $entityManager = $this->searchHistoryRepository->getEntityManager();
        $entityManager->createQuery('DELETE FROM App\Entity\SearchHistory')->execute();

        return $this->json([
            'message' => 'All search history cleared'
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $search = $this->searchHistoryRepository->find($id);

        if (!$search) {
            return $this->json([
                'error' => 'Search history not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->searchHistoryRepository->remove($search, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
