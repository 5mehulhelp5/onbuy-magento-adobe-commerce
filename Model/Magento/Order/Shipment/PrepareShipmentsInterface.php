<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Order\Shipment;

interface PrepareShipmentsInterface
{
    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param \M2E\OnBuy\Model\Order $onBuyOrder
     * @param \Magento\Sales\Model\Order\Item[] $itemsToShip
     *
     * @return \Magento\Sales\Model\Order\Shipment[]
     */
    public function prepareShipments(
        \Magento\Sales\Model\Order $magentoOrder,
        \M2E\OnBuy\Model\Order $onBuyOrder,
        array $itemsToShip
    ): array;
}
