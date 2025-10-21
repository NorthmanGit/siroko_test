<?php
namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Model\Cart;

interface CartRepositoryInterface
{
    public function save(Cart $cart): void;
    public function findById(string $id): ?Cart;
}