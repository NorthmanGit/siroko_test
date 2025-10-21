<?php
namespace App\Cart\Application\Command;

class AddItemToCartCommand
{
    public string $cartId;
    public string $productId;
    public int $quantity;

    public function __construct(string $cartId, string $productId, int $quantity)
    {
        $this->cartId = $cartId;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }
}