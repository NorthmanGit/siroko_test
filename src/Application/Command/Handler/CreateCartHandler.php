<?php

namespace App\Application\Command\Handler;

use App\Application\Command\CreateCartCommand;
use App\Domain\Entity\Cart;
use App\Domain\Repository\CartRepositoryInterface;

class CreateCartHandler
{
    private CartRepositoryInterface $repository;


    public function __construct(CartRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateCartCommand $command): void
    {
        // Create a new Cart aggregate with the generated ID
        $cart = new Cart($command->getId());

        // Optionally, you could set initial metadata like user ID or timestamps

        // Persist via repository
        $this->repository->save($cart);
    }
}
