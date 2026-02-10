<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SearchHistoryControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\SearchHistory')->execute();
    }

    public function testCreateSearchHistory(): void
    {
        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
            'country' => 'FR',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('Paris', $data['data']['city']);
        $this->assertEquals('FR', $data['data']['country']);
        $this->assertEquals(48.8566, $data['data']['latitude']);
        $this->assertEquals(2.3522, $data['data']['longitude']);
        $this->assertNotNull($data['data']['id']);
    }

    public function testCreateSearchHistoryWithoutCoordinates(): void
    {
        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testListSearchHistory(): void
    {
        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]));

        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Lyon',
            'latitude' => 45.7640,
            'longitude' => 4.8357,
        ]));

        $this->client->request('GET', '/api/history');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
        $this->assertEquals('Lyon', $data['data'][0]['city']);
    }

    public function testListSearchHistoryWithLimit(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->client->request('POST', '/api/history', [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], json_encode([
                'city' => "City $i",
                'latitude' => 48.0 + $i,
                'longitude' => 2.0 + $i,
            ]));
        }

        $this->client->request('GET', '/api/history?limit=5');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertCount(5, $data['data']);
    }

    public function testDeleteSearchHistory(): void
    {
        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $id = $createData['data']['id'];

        $this->client->request('DELETE', "/api/history/$id");

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/api/history');
        $listData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertCount(0, $listData['data']);
    }

    public function testDeleteNonExistentSearchHistory(): void
    {
        $this->client->request('DELETE', '/api/history/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testClearAllSearchHistory(): void
    {
        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]));

        $this->client->request('POST', '/api/history', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Lyon',
            'latitude' => 45.7640,
            'longitude' => 4.8357,
        ]));

        $this->client->request('GET', '/api/history');
        $beforeData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $beforeData['data']);

        $this->client->request('DELETE', '/api/history/clear');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);

        $this->client->request('GET', '/api/history');
        $afterData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertCount(0, $afterData['data']);
    }
}
