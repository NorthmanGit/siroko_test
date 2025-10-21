<?php
namespace App\Cart\Application\CommandHandler;

use App\Cart\Application\Command\AddItemToCartCommand;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Repository\CartRepositoryInterface;

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
            $cart = new Cart($command->cartId);
        }

        $cartItem = new CartItem($command->productId, $command->quantity);
        $cart->addItem($cartItem);

        $this->repository->save($cart);
    }
}