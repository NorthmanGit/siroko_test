<?php
namespace App\Domain\Entity;

class Cart
{
    private string $id;
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** @return CartItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(CartItem $item): void
    {
        $existing = $this->findItem($item->getProductId());

        if ($existing) {
            $existing->increaseQuantity($item->getQuantity());
        } else {
            $this->items[] = $item;
        }
    }

    private function findItem(string $productId): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getProductId() === $productId) {
                return $item;
            }
        }
        return null;
    }
}
