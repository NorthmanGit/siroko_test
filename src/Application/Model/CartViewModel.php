<?php

namespace App\Application\Model;

use App\Domain\Entity\Cart;

final class CartViewModel implements ViewModelInterface
{
    private string $cartId;
    private array $items;

    public function __construct(string $cartId, array $items)
    {
        $this->cartId = $cartId;
        $this->items = $items;
    }

    public function getCartId(): string
    {
        return $this->cartId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public static function fromDomainCart(Cart $cart): ?ViewModelInterface
    {
        // Simple example: transform Domain Cart → ViewModel
        $items = [];

        foreach ($cart->getItems() as $item) {
            $items[] = [
                'productId' => $item->getProductId(),
                'quantity'  => $item->getQuantity(),
            ];
        }

        return new self(
            $cart->getId(),
            $items
        );
    }

    public function toArray(): array
    {
        return [
            'cartId' => $this->cartId,
            'items'  => $this->items,
        ];
    }
}
