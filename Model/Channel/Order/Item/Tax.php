<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order\Item;

class Tax
{
    public ?float $taxDelivery;
    public ?float $taxProduct;
    public ?float $taxTotal;
    public ?float $taxScheme;

    public function __construct(
        ?float $taxDelivery,
        ?float $taxProduct,
        ?float $taxTotal,
        ?float $taxScheme
    ) {
        $this->taxDelivery = $taxDelivery;
        $this->taxProduct = $taxProduct;
        $this->taxTotal = $taxTotal;
        $this->taxScheme = $taxScheme;
    }
}
