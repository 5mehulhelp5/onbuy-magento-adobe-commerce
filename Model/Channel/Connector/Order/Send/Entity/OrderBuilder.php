<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity;

class OrderBuilder
{
    private string $onBuyOrderId;
    private array $tracking = [];
    private array $products = [];

    public static function create(): self
    {
        return new self();
    }

    public function build(): Order
    {
        $this->validate();

        return new Order(
            $this->onBuyOrderId,
            $this->tracking,
            $this->products,
        );
    }

    public function setOrderId(string $onBuyOrderId): self
    {
        $this->onBuyOrderId = $onBuyOrderId;

        return $this;
    }

    public function setTrackingInfo(string $supplierName, string $trackingNumber): self
    {
        $tracking = [
            'carrier_name' => $supplierName,
            'tracking_number' => $trackingNumber,
        ];

        $this->tracking = $tracking;

        return $this;
    }

    public function setProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Item[] $orderItems
     * @param array $orderItemsForShip
     *
     * @return array
     */
    public function buildProducts(array $orderItems, array $orderItemsForShip): array
    {
        $shipmentItems = [];
        foreach ($orderItemsForShip as $shipmentItem) {
            $itemId = (int)$shipmentItem['item_id'];
            $quantityToShip = (int)$shipmentItem['qty'];
            $shipmentItems[$itemId] = $quantityToShip;
        }

        $products = array_filter(array_map(static function ($orderItem) use ($shipmentItems) {
            $itemId = $orderItem->getId();

            return [
                'sku' => $orderItem->getChannelSku(),
                'qty' => $shipmentItems[$itemId],
            ];
        }, $orderItems));

        return array_values($products);
    }

    public function getTrackingInfo(): array
    {
        return $this->tracking;
    }

    private function validate(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->onBuyOrderId)) {
            throw new \M2E\OnBuy\Model\Exception\Logic('OnBuy order ID not set');
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (empty($this->tracking)) {
            throw new \M2E\OnBuy\Model\Exception\Logic('OnBuy tracking is empty');
        }
    }
}
