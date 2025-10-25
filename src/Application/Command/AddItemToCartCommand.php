<?php
namespace App\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

class AddItemToCartCommand
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $cartId;
    #[Assert\NotBlank]
    public string $productId;
    #[Assert\Positive]
    public int $quantity;

    public function __construct(string $cartId, string $productId, int $quantity)
    {
        $this->cartId = $cartId;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }
}