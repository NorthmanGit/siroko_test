<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;
    public function find(int $id): ?Order;

    /**
     * Checkout complet: guardar l’ordre i eliminar el carretó associat.
     */
    public function checkout(Order $order, string $cartId): Order;
}
