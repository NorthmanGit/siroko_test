<?php
namespace App\Application\Command\Handler;

use App\Application\Command\CheckoutCommand;
use App\Domain\Repository\CartRepositoryInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderItem;
use RuntimeException;
class CheckoutHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(CheckoutCommand $command): int
    {
        $cart = $this->cartRepository->findById($command->cartId);
        if (!$cart) {
            throw new RuntimeException("Cart not found: {$command->cartId}");
        }

        $order = new Order($command->cartId);
        foreach ($cart->getItems() as $item) {
            $order->addItem(new OrderItem($item->getProductId(), $item->getQuantity()));
        }

        $persistedOrder = $this->orderRepository->checkout($order, $cart->getId());

        return $persistedOrder->getId();
    }
}
