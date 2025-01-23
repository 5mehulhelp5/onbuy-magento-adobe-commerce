<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Price;

class Result extends \M2E\OnBuy\Model\Product\DataProvider\AbstractResult
{
    public function getValue(): Value
    {
        return $this->value;
    }
}
