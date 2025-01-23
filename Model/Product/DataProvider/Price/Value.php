<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Price;

class Value
{
    public float $price;
    public string $currencyCode;

    public function __construct(
        float $price,
        string $currencyCode
    ) {
        $this->price = $price;
        $this->currencyCode = $currencyCode;
    }
}
