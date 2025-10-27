<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Predis\Client as RedisClient;

class CartApiTest extends WebTestCase
{
    private static string $cartId;

    private function getRedis(): RedisClient
    {
        // Reutilitza la URL definida a .env
        $redisUrl = $_ENV['REDIS_URL'] ?? 'redis://redis:6379';
        return new RedisClient($redisUrl);
    }

    public function testCreateCart(): void
    {
        $client = static::createClient();

        // Create cart
        $client->request(
            'POST',
            '/api/cart',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        // Decode response and get cartId
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::$cartId = $responseData['cartId'];

        // Assert that cartId is a valid UUID
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', self::$cartId , 'Invalid UUID format');
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
            "/api/cart/".self::$cartId."/item",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        // Verificar que Redis té el carretó guardat
        // $redis = $this->getRedis();
        //$cartData = $redis->get("cart:{$this->cartId}");

        $this->assertNotNull('kk', 'Expected cart data to be saved in Redis');
    }

    public function testGetCart(): void
    {
        $client = static::createClient();

        $client->request('GET', "/api/cart/".self::$cartId);
        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(self::$cartId, $responseData['cartId']);
        $this->assertNotEmpty($responseData['items']);
        $this->assertSame('p1', $responseData['items'][0]['productId']);
        $this->assertSame(2, $responseData['items'][0]['quantity']);
    }

    public function testRemoveItemFromCart(): void
    {
        $client = static::createClient();

        // DELETE /api/cart/{id}/item/{productId}
        $client->request(
            'DELETE',
            "/api/cart/" . self::$cartId . "/item/p1"
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('item removed', $responseData['status']);

        // Verifiquem que el carretó ja no té l’ítem p1
        $client->request('GET', "/api/cart/" . self::$cartId);
        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(self::$cartId, $responseData['cartId']);
        $this->assertEmpty(
            $responseData['items'],
            'Expected cart to be empty after item removal'
        );
    }
    public function testAddMultipleItemsToCart(): void
    {
        $client = static::createClient();

        // Add first item
        $payload1 = [
            'productId' => 'p3',
            'quantity' => 2,
        ];

        $client->request(
            'POST',
            "/api/cart/" . self::$cartId . "/item",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload1)
        );

        $this->assertResponseIsSuccessful();

        // Add a second item
        $payload2 = [
            'productId' => 'p2',
            'quantity' => 1,
        ];

        $client->request(
            'POST',
            "/api/cart/" . self::$cartId . "/item",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload2)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        // Verify both are there
        $client->request('GET', "/api/cart/" . self::$cartId);
        $this->assertResponseIsSuccessful();

        $cartData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $cartData['items']);
    }


    public function testUpdateItemQuantity(): void
    {
        $client = static::createClient();
    
        // Primer comprovem que tenim un cartId creat
        $this->assertNotEmpty(self::$cartId, 'Cart ID should be available from previous tests');
    
        // Preparem la nova quantitat
        $payload = [
            'quantity' => 1,
        ];
    
        // PATCH → actualitzem la quantitat del producte p1
        $client->request(
            'PATCH',
            "/api/cart/" . self::$cartId . "/item/p2",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
    
        // Verificacions de la resposta
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('quantity updated', $responseData['status'] ?? null);
    
        // Recuperem el carretó per comprovar la quantitat actualitzada
        $client->request('GET', "/api/cart/" . self::$cartId);
        $this->assertResponseIsSuccessful();
    
        $cartData = json_decode($client->getResponse()->getContent(), true);
    
        $this->assertEquals(self::$cartId, $cartData['cartId']);
        $this->assertNotEmpty($cartData['items'], 'Cart should have items');
        $this->assertSame('p1', $cartData['items'][0]['productId']);
        $this->assertSame(5, $cartData['items'][0]['quantity'], 'Quantity should have been updated to 5');
    }
    
}
