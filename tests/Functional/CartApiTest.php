<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Predis\Client as RedisClient;

class CartApiTest extends WebTestCase
{
    private static string $cartId;
    public function testCreateCart(): void
    {
        $client = static::createClient();

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

        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::$cartId = $responseData['cartId'];

        // Assert that cartId is a valid UUID
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', self::$cartId , 'Invalid UUID format');
    }

    public function testAddItemToCart(): void
    {
        $client = static::createClient();

        $payload = [
            'productId' => 'p2',
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
    }

    public function testGetCart(): void
    {
        $client = static::createClient();

        $client->request('GET', "/api/cart/".self::$cartId);
        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(self::$cartId, $responseData['cartId']);
        $this->assertNotEmpty($responseData['items']);
        $this->assertSame('p2', $responseData['items'][0]['productId']);
        $this->assertSame(2, $responseData['items'][0]['quantity']);
    }

    public function testRemoveItemFromCart(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            "/api/cart/" . self::$cartId . "/item/p2"
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('item removed', $responseData['status']);

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

        $payload1 = [
            'productId' => 'p2',
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

        $payload2 = [
            'productId' => 'p1',
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
    
        $this->assertNotEmpty(self::$cartId, 'Cart ID should be available from previous tests');
    
        $payload = [
            'quantity' => 1,
        ];

        $client->request(
            'PATCH',
            "/api/cart/" . self::$cartId . "/item/p1",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
    
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('quantity updated', $responseData['status'] ?? null);

        $client->request('GET', "/api/cart/" . self::$cartId);
        $this->assertResponseIsSuccessful();
    
        $cartData = json_decode($client->getResponse()->getContent(), true);
    
        $this->assertEquals(self::$cartId, $cartData['cartId']);
        $this->assertNotEmpty($cartData['items'], 'Cart should have items');
        $this->assertSame('p1', $cartData['items'][1]['productId']);
        $this->assertSame(1, $cartData['items'][1]['quantity'], 'Quantity should have been updated to 3');
    }
    public function testCheckoutCart(): void
    {
        $client = static::createClient();

        $this->assertNotEmpty(self::$cartId, 'Cart ID should be available from previous tests');

        $client->request(
            'POST',
            "/api/cart/" . self::$cartId . "/checkout",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
    
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('orderId', $responseData, 'Response should contain an orderId');
        $this->assertNotEmpty($responseData['orderId'], 'Order ID should not be empty');
        $this->assertIsInt($responseData['orderId'], 'Order ID should be an integer');

        $client->request('GET', "/api/cart/" . self::$cartId);
        $this->assertResponseStatusCodeSame(404, 'Cart should be deleted after checkout');
    }
        
}
