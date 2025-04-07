<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Attributes;

class Item
{
    private int $valueId;

    public function __construct(
        int $valueId
    ) {
        $this->valueId = $valueId;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }
}
