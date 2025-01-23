<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order\Item;

class Price
{
    public float $deliveryTotal;
    public float $unit;
    public float $total;

    public function __construct(
        float $deliveryTotal,
        float $unit,
        float $total
    ) {
        $this->deliveryTotal = $deliveryTotal;
        $this->unit = $unit;
        $this->total = $total;
    }
}
