<?php

namespace App\Controller\Api;

use App\Service\OpenMeteoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/weather')]
class WeatherController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function __invoke(OpenMeteoClient $client): JsonResponse
    {
        $data = $client->getWeather(47.3220, 5.0415);

        return $this->json($data);
    }
}
