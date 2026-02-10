<?php

namespace App\Controller\Api;

use App\Service\GeocodingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/geocoding', name: 'api_geocoding_')]
class GeocodingController extends AbstractController
{
    public function __construct(
        private readonly GeocodingService $geocodingService
    ) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty(trim($query))) {
            return $this->json([
                'error' => 'Query parameter "q" is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $results = $this->geocodingService->searchCity($query);

        return $this->json([
            'data' => $results
        ]);
    }
}
