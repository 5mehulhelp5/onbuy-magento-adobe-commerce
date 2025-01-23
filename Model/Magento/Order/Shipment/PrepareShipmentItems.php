<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Order\Shipment;

class PrepareShipmentItems
{
    public function getQtyToShip(\M2E\OnBuy\Model\Order $onBuyOrder, $itemsToShip): array
    {
        $dispatchedQtyMap = [];
        /** @var \M2E\OnBuy\Model\Order\Item $onbuyItem */
        foreach ($onBuyOrder->getItems() as $onbuyItem) {
            $magentoProduct = $onbuyItem->getMagentoProduct();
            if (!$magentoProduct || !$magentoProduct->getProductId()) {
                continue;
            }
            if ($onbuyItem->getQtyDispatched() > 0) {
                $dispatchedQtyMap[$magentoProduct->getProductId()] = $onbuyItem->getQtyDispatched();
            }
        }

        $itemsQtyToShip = [];
        foreach ($itemsToShip as $magentoOrderItem) {
            $productId = (int)$magentoOrderItem->getProductId();
            if (isset($dispatchedQtyMap[$productId])) {
                $magentoAllowQty = (int)$magentoOrderItem->getQtyToShip();
                /**
                 * @psalm-suppress RedundantCast
                 */
                $qty = (int)$dispatchedQtyMap[$productId] - (int)$magentoOrderItem->getQtyShipped();

                if ($qty > $magentoAllowQty) {
                    $qty = $magentoAllowQty;
                }

                if ($qty === 0) {
                    continue;
                }

                $itemsQtyToShip[$productId] = $qty;
            }
        }

        return $itemsQtyToShip;
    }
}
