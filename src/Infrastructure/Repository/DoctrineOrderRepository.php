<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\CartRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepositoryInterface $cartRepository
    ) {}

    public function save(Order $order): void
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    public function find(int $id): ?Order
    {
        return $this->em->find(Order::class, $id);
    }

    public function checkout(Order $order, string $cartId): Order
    {
        $this->em->beginTransaction();
    try {
        $this->em->persist($order);
        $this->em->flush();
        $this->cartRepository->delete($cartId);

        $this->em->commit();
    } catch (\Throwable $e) {
        $this->em->rollback();
        throw $e;
    }

    return $order;
    }
}
