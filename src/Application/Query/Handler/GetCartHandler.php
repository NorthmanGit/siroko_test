<?php

namespace App\Application\Query\Handler;

use App\Application\Model\CartViewModel;
use App\Application\Query\GetCartQuery;
use App\Domain\Repository\CartRepositoryInterface;

final class GetCartHandler
{
    public function __construct(private CartRepositoryInterface $repository)
    {
    }

    public function __invoke(GetCartQuery $query): ?CartViewModel
    {
        // Allow multiple query strategies
        $cart = null;

        if ($query->cartId) {
            $cart = $this->repository->findById($query->cartId);
        }
        if (!$cart) {
            return null; // No cart found
        }

        // Transform Domain Entity → View Model
        return CartViewModel::fromDomainCart($cart);
    }
}
