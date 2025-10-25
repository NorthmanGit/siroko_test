<?php
namespace App\Application\Command\Handler;

use App\Application\Command\AddItemToCartCommand;
use App\Domain\Entity\Cart;
use App\Domain\Entity\CartItem;
use App\Domain\Repository\CartRepositoryInterface;
use Exception;

class AddItemToCartHandler
{
    private CartRepositoryInterface $repository;

    public function __construct(CartRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    public function __invoke(AddItemToCartCommand $command): void
    {
        $cart = $this->repository->findById($command->cartId);

        if (!$cart) {
            throw new Exception($command->cartId);
        }

        $cartItem = new CartItem($command->productId, $command->quantity);
        $cart->addItem($cartItem);

        $this->repository->save($cart);
    }
}