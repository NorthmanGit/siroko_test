<?php
namespace App\Cart\Domain\Model;

class CartItem
{
    private string $productId;
    private int $quantity;

    public function __construct(string $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function increaseQuantity(int $amount): void
    {
        $this->quantity += $amount;
    }
}
    