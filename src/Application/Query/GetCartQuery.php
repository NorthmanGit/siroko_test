<?php
namespace App\Application\Query;


use Symfony\Component\Validator\Constraints as Assert;

class GetCartQuery
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $cartId;

    public function __construct(string $cartId)
    {
        $this->cartId = $cartId;
    }
}