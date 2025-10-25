<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Predis\Client as RedisClient;

class CartApiTest extends WebTestCase
{
    private string $cartId = 'test-cart';

    private function getRedis(): RedisClient
    {
        // Reutilitza la URL definida a .env
        $redisUrl = $_ENV['REDIS_URL'] ?? 'redis://redis:6379';
        return new RedisClient($redisUrl);
    }

    public function testAddItemToCart(): void
    {
        $client = static::createClient();

        $payload = [
            'productId' => 'p1',
            'quantity' => 2,
        ];

        $client->request(
            'POST',
            "/api/cart/{$this->cartId}/items",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        // Verificar que Redis té el carretó guardat
        $redis = $this->getRedis();
        $cartData = $redis->get("cart:{$this->cartId}");

        $this->assertNotNull($cartData, 'Expected cart data to be saved in Redis');
    }

    public function testGetCart(): void
    {
        $client = static::createClient();

        $client->request('GET', "/api/cart/{$this->cartId}");
        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($this->cartId, $responseData['cartId']);
        $this->assertNotEmpty($responseData['items']);
        $this->assertSame('p1', $responseData['items'][0]['productId']);
        $this->assertSame(2, $responseData['items'][0]['quantity']);
    }
}
