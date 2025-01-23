<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class SalesFee
{
    public float $ex;
    public float $inc;

    public function __construct(
        float $ex,
        float $inc
    ) {
        $this->ex = $ex;
        $this->inc = $inc;
    }
}
