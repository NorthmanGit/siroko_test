<?php
namespace App\Application\Infrastructure\Repository;

use App\Domain\Model\Cart;
use App\Domain\Repository\CartRepositoryInterface;

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
