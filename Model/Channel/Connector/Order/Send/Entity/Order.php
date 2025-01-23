<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity;

class Order
{
    public string $id;
    public array $tracking;
    public array $products;

    public function __construct(
        string $orderId,
        array $tracking,
        array $products
    ) {
        $this->id = $orderId;
        $this->tracking = $tracking;
        $this->products = $products;
    }
}
