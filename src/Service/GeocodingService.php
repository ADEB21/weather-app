<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {}

    /**
     * @return array{name: string, country: string, latitude: float, longitude: float}[]
     */
    public function searchCity(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $response = $this->client->request('GET', 'https://geocoding-api.open-meteo.com/v1/search', [
            'query' => [
                'name' => $query,
                'count' => 10,
                'language' => 'fr',
                'format' => 'json',
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['results']) || !is_array($data['results'])) {
            return [];
        }

        return array_map(function (array $result): array {
            return [
                'name' => $result['name'] ?? '',
                'country' => $result['country'] ?? '',
                'admin1' => $result['admin1'] ?? null,
                'latitude' => (float) ($result['latitude'] ?? 0),
                'longitude' => (float) ($result['longitude'] ?? 0),
            ];
        }, $data['results']);
    }
}
