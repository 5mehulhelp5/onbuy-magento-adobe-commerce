<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Order\Item;

class Tracking
{
    public string $supplierName;
    public string $trackingNumber;
    public ?string $trackingUrl;

    public function __construct(
        string $supplierName,
        string $trackingNumber,
        ?string $trackingUrl
    ) {
        $this->supplierName = $supplierName;
        $this->trackingNumber = $trackingNumber;
        $this->trackingUrl = $trackingUrl;
    }
}
