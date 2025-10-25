<?php

namespace App\Application\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

class CreateCartCommand
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $id;

    public function __construct()
    {
        // Generate UUID here (Symfony >= 6.2 supports it natively)
        $this->id = Uuid::v1();
    }

    public function getId(): string
    {
        return $this->id;
    }
}