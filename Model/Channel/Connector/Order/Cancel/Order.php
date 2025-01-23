<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Cancel;

class Order
{
    private string $orderId;

    public function __construct(
        string $orderId
    ) {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
