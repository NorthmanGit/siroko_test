<?php
namespace App\Cart\Domain\Model;

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

    public function addItem(CartItem $item): void
    {
        foreach ($this->items as $existingItem) {
            if ($existingItem->getProductId() === $item->getProductId()) {
                $existingItem->increaseQuantity($item->getQuantity());
                return;
            }
        }
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    // Otros métodos dominio: removeItem, total, etc.
}