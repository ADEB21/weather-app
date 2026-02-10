<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GeocodingControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearchCityWithValidQuery(): void
    {
        $this->client->request('GET', '/api/geocoding/search?q=Paris');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        
        if (count($data['data']) > 0) {
            $firstResult = $data['data'][0];
            $this->assertArrayHasKey('name', $firstResult);
            $this->assertArrayHasKey('country', $firstResult);
            $this->assertArrayHasKey('latitude', $firstResult);
            $this->assertArrayHasKey('longitude', $firstResult);
        }
    }

    public function testSearchCityWithoutQuery(): void
    {
        $this->client->request('GET', '/api/geocoding/search');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSearchCityWithEmptyQuery(): void
    {
        $this->client->request('GET', '/api/geocoding/search?q=');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testSearchMultipleCities(): void
    {
        $cities = ['Lyon', 'Marseille', 'Toulouse'];
        
        foreach ($cities as $city) {
            $this->client->request('GET', "/api/geocoding/search?q=$city");
            
            $this->assertResponseIsSuccessful();
            
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('data', $data);
            $this->assertGreaterThan(0, count($data['data']));
        }
    }
}
