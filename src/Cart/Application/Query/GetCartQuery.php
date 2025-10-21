<?php
namespace App\Cart\Application\Query;

class GetCartQuery
{
    public string $cartId;

    public function __construct(string $cartId)
    {
        $this->cartId = $cartId;
    }
}