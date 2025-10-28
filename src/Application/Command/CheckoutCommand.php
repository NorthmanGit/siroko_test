<?php

namespace App\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;
class CheckoutCommand
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $cartId;

    public function __construct(
        string $cartId
    ) {
        $this->cartId = $cartId;
    }
}
