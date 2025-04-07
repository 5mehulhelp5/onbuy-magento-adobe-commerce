<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Attributes;

class Result extends \M2E\OnBuy\Model\Product\DataProvider\AbstractResult
{
    public function getValue(): array
    {
        return $this->value;
    }
}
