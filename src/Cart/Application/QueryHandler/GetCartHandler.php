<?php
namespace App\Cart\Application\QueryHandler;

use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Domain\Repository\CartRepositoryInterface;

class GetCartHandler
{
    private CartRepositoryInterface $repository;

    public function __construct(CartRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetCartQuery $query)
    {
        $cart = $this->repository->findById($query->cartId);
        return $cart;
    }
}
