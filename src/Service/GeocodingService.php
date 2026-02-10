<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private const API_URL = 'https://geocoding-api.open-meteo.com/v1/search';
    private const MAX_RESULTS = 10;
    private const LANGUAGE = 'fr';

    public function __construct(
        private readonly HttpClientInterface $client
    ) {}

    /**
     * @return array{name: string, country: string, latitude: float, longitude: float}[]
     */
    public function searchCity(string $query): array
    {
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [
                'name' => $query,
                'count' => self::MAX_RESULTS,
                'language' => self::LANGUAGE,
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
