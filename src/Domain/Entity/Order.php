<?php

namespace App\Domain\Entity;

use App\Infrastructure\Repository\DoctrineOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineOrderRepository::class)]
#[ORM\Table(name: "orders")]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "uuid", unique: true)]
    private string $cartId;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderItem::class, cascade: ["persist"], orphanRemoval: true)]
    private Collection $items;

    public function __construct(string $cartId)
    {
        $this->cartId = $cartId;
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCartId(): string { return $this->cartId; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
    }
}
