<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FavoriteControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\Favorite')->execute();
    }

    public function testCreateFavorite(): void
    {
        $this->client->request('POST', '/api/favorites', [], [], [
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
        $this->assertNotNull($data['data']['id']);
    }

    public function testCreateDuplicateFavorite(): void
    {
        $favoriteData = [
            'city' => 'Lyon',
            'country' => 'FR',
            'latitude' => 45.7640,
            'longitude' => 4.8357,
        ];

        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($favoriteData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($favoriteData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testListFavorites(): void
    {
        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ]));

        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Lyon',
            'latitude' => 45.7640,
            'longitude' => 4.8357,
        ]));

        $this->client->request('GET', '/api/favorites');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function testCheckFavoriteExists(): void
    {
        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Marseille',
            'latitude' => 43.2965,
            'longitude' => 5.3698,
        ]));

        $this->client->request('GET', '/api/favorites/check?latitude=43.2965&longitude=5.3698');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($data['isFavorite']);
        $this->assertNotNull($data['favoriteId']);
    }

    public function testCheckFavoriteNotExists(): void
    {
        $this->client->request('GET', '/api/favorites/check?latitude=50.0&longitude=3.0');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($data['isFavorite']);
        $this->assertNull($data['favoriteId']);
    }

    public function testDeleteFavorite(): void
    {
        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Toulouse',
            'latitude' => 43.6047,
            'longitude' => 1.4442,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $id = $createData['data']['id'];

        $this->client->request('DELETE', "/api/favorites/$id");

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', '/api/favorites');
        $listData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertCount(0, $listData['data']);
    }

    public function testDeleteNonExistentFavorite(): void
    {
        $this->client->request('DELETE', '/api/favorites/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateFavoriteWithoutCoordinates(): void
    {
        $this->client->request('POST', '/api/favorites', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'city' => 'Nice',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
