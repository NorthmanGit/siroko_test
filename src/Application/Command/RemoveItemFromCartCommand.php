<?php
namespace App\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;

class RemoveItemFromCartCommand
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $cartId;

    #[Assert\NotBlank]
    public string $productId;

    public function __construct(string $cartId, string $productId)
    {
        $this->cartId = $cartId;
        $this->productId = $productId;
    }
}
