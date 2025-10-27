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

    public function removeItem(string $productId): void
    {
        $this->items = array_filter(
            $this->items,
            fn (CartItem $item) => $item->getProductId() !== $productId
        );
    }

    public function updateItemQuantity(string $productId, int $newQuantity): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getProductId() === $productId) {
                if ($newQuantity <= 0) {
                    // Si la quantitat nova és 0 o negativa, eliminem l'ítem
                    unset($this->items[$index]);
                    $this->items = array_values($this->items);
                    return;
                }

                $item->setQuantity($newQuantity);
                return;
            }
        }

        throw new \RuntimeException("Product {$productId} not found in cart {$this->id}");
    }

}
