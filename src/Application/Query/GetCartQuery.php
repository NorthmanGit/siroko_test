<?php
namespace App\Application\Query;


use Symfony\Component\Validator\Constraints as Assert;
use App\Application\QueryCommandInterface;
class GetCartQuery implements QueryCommandInterface
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $cartId;

    public function __construct(string $cartId)
    {
        $this->cartId = $cartId;
    }
}