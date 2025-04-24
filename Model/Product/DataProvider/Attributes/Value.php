<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Attributes;

class Value
{
    /** @var \M2E\OnBuy\Model\Product\DataProvider\Attributes\Item[] */
    public array $items;

    public function __construct(
        array $items
    ) {
        $this->items = $items;
    }
}
