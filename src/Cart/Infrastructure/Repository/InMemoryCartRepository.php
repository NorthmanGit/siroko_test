<?php
namespace App\Cart\Infrastructure\Repository;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Repository\CartRepositoryInterface;

class InMemoryCartRepository implements CartRepositoryInterface
{
    private array $storage = [];

    public function save(Cart $cart): void
    {
        $this->storage[$cart->getId()] = $cart;
    }

    public function findById(string $id): ?Cart
    {
        return $this->storage[$id] ?? null;
    }
}
