<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class Price
{
    public float $total;
    public float $subtotal;
    public float $delivery;
    public float $discount;

    public function __construct(
        float $total,
        float $subtotal,
        float $delivery,
        float $discount
    ) {
        $this->total = $total;
        $this->subtotal = $subtotal;
        $this->delivery = $delivery;
        $this->discount = $discount;
    }
}
