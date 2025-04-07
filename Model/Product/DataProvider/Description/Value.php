<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Description;

class Value
{
    public string $description;

    public function __construct(
        string $description
    ) {
        $this->description = $description;
    }
}
