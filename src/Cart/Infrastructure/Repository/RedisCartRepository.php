<?php

namespace App\Cart\Infrastructure\Repository;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use Predis\Client;

class RedisCartRepository implements CartRepositoryInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function save(Cart $cart): void
    {
        $key = $this->getKey($cart->getId());
        $data = serialize($cart);
        
        // Guardem el carrito amb una expiració (TTL) de 1 hora (3600 segons)
        $this->redis->setex($key, 3600, $data);
    }

    public function findById(string $id): ?Cart
    {
        $data = $this->redis->get($this->getKey($id));

        if (!$data) {
            return null;
        }

        return unserialize($data);
    }

    private function getKey(string $id): string
    {
        return sprintf('cart:%s', $id);
    }
}
