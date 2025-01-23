<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Product;

class ProductCollection
{
    /** @var \M2E\OnBuy\Model\Channel\Product[] */
    private array $products = [];

    public function empty(): bool
    {
        return empty($this->products);
    }

    public function has(string $sku): bool
    {
        return isset($this->products[$sku]);
    }

    public function add(\M2E\OnBuy\Model\Channel\Product $product): void
    {
        $this->products[$product->getSku()] = $product;
    }

    public function get(string $sku): \M2E\OnBuy\Model\Channel\Product
    {
        return $this->products[$sku];
    }

    public function remove(string $sku): void
    {
        unset($this->products[$sku]);
    }

    /**
     * @return \M2E\OnBuy\Model\Channel\Product[]
     */
    public function getAll(): array
    {
        return array_values($this->products);
    }

    /**
     * @return string[]
     */
    public function getProductsSku(): array
    {
        return array_keys($this->products);
    }
}
