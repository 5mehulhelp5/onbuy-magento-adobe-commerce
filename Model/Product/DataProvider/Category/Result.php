<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Category;

class Result extends \M2E\OnBuy\Model\Product\DataProvider\AbstractResult
{
    public function getValue(): int
    {
        return $this->value;
    }
}
