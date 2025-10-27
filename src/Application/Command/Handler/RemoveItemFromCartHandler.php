<?php
namespace App\Application\Command\Handler;

use App\Application\Command\RemoveItemFromCartCommand;
use App\Domain\Repository\CartRepositoryInterface;
use Exception;

class RemoveItemFromCartHandler
{
    public function __construct(private CartRepositoryInterface $repository) {}

    public function __invoke(RemoveItemFromCartCommand $command): void
    {
        $cart = $this->repository->findById($command->cartId);
        if (!$cart) {
            throw new Exception(sprintf('Cart not found: %s', $command->cartId));
        }

        $cart->removeItem($command->productId);
        $this->repository->save($cart);
    }
}
