<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order;

class Tax
{
    public ?float $taxTotal;
    public ?float $taxSubtotal;
    public ?float $taxDelivery;

    public function __construct(
        ?float $taxTotal,
        ?float $taxSubtotal,
        ?float $taxDelivery
    ) {
        $this->taxTotal = $taxTotal;
        $this->taxSubtotal = $taxSubtotal;
        $this->taxDelivery = $taxDelivery;
    }
}
