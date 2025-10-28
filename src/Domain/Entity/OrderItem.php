<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "order_items")]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Order $order;

    #[ORM\Column(type: "string", length: 255)]
    private string $productId;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    public function __construct(string $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getProductId(): string { return $this->productId; }

    public function getQuantity(): int { return $this->quantity; }

    public function setOrder(Order $order): void { $this->order = $order; }
}
