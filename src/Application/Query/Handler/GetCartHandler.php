<?php

namespace App\Application\Query\Handler;

use App\Application\Model\CartViewModel;
use App\Application\Query\GetCartQuery;
use App\Domain\Repository\CartRepositoryInterface;
use App\Application\Model\ViewModelInterface;
use App\Application\Query\Handler\QueryHandlerInterface;
use App\Application\QueryCommandInterface;

class GetCartHandler implements QueryHandlerInterface
{
    private CartRepositoryInterface $repository;
    public function __construct( CartRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(QueryCommandInterface $query): ?ViewModelInterface
    {  
                // Type check to ensure $query is of type GetCartQuery
                if (!$query instanceof GetCartQuery) {
                    throw new \InvalidArgumentException('Expected GetCartQuery');
                }
            // Allow multiple query strategies
            $cart = null;   
            if ($query->cartId) {
                $cart = $this->repository->findById($query->cartId);
            }
            if (!$cart) {
                return null; // No cart found
            }
            
            // Transform Domain Entity → View Model
            $result =  CartViewModel::fromDomainCart($cart); 
            return $result;
    }
}
