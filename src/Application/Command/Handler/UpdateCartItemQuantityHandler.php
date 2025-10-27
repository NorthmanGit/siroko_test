<?php

namespace App\Application\Command\Handler;

use App\Application\Command\UpdateCartItemQuantityCommand;
use App\Domain\Repository\CartRepositoryInterface;
use Exception;

class UpdateCartItemQuantityHandler
{
    private CartRepositoryInterface $repository;

    public function __construct(CartRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateCartItemQuantityCommand $command): void
    {
        $cart = $this->repository->findById($command->cartId);

        if (!$cart) {
            throw new Exception("Cart not found: {$command->cartId}");
        }

        $cart->updateItemQuantity($command->productId, $command->quantity);

        $this->repository->save($cart);
    }
}
