<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Cart;

interface CartRepositoryInterface
{
    public function save(Cart $cart): void;
    public function findById(string $id): ?Cart;
}