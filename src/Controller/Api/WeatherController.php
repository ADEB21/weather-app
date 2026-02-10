<?php

namespace App\Controller\Api;

use App\Service\OpenMeteoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/weather', name: 'api_weather')]
class WeatherController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function __invoke(Request $request, OpenMeteoClient $client): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');

        if ($latitude === null || $longitude === null) {
            return $this->json([
                'error' => 'latitude and longitude parameters are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = $client->getWeather((float) $latitude, (float) $longitude);

        return $this->json($data);
    }
}
