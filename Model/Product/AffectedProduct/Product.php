<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\AffectedProduct;

class Product
{
    private \M2E\OnBuy\Model\Product $product;

    public function __construct(
        \M2E\OnBuy\Model\Product $product
    ) {
        $this->product = $product;
    }

    public function getProduct(): \M2E\OnBuy\Model\Product
    {
        return $this->product;
    }
}
