<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Shipment;

class ItemLoader
{
    /**
     * @return \M2E\OnBuy\Model\Order\Item[]
     */
    public function loadItemsByShipment(
        \M2E\OnBuy\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment,
        ?\M2E\OnBuy\Model\Order\Change $existOrderChange
    ): array {
        $result = [];
        if ($existOrderChange !== null) {
            foreach ($existOrderChange->getOrderItemsIdsForShipping() as $orderItemId) {
                $result[] = $order->getItem($orderItemId);
            }

            return $result;
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            $orderItem = $shipmentItem->getOrderItem();
            if ($orderItem->getParentItemId() !== null) {
                continue;
            }

            $orderItems = $this->loadItems($order, $shipmentItem);
            if (empty($orderItems)) {
                continue;
            }

            array_push($result, ...$orderItems);
        }

        return $result;
    }

    /**
     * @return \M2E\OnBuy\Model\Order\Item[]
     */
    private function loadItems(
        \M2E\OnBuy\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): array {
        $magentoProductId = (int)$shipmentItem->getProductId();

        $result = [];
        foreach ($order->getItems() as $item) {
            $magentoProduct = $item->getMagentoProduct();
            if (
                $magentoProduct === null
                || $magentoProductId !== $magentoProduct->getProductId()
            ) {
                continue;
            }

            if (!$this->canItemBeShipped($item, $shipmentItem)) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    private function canItemBeShipped(
        \M2E\OnBuy\Model\Order\Item $orderItem,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): bool {
        $purchasedQty = $orderItem->getQtyPurchased();
        $dispatchedQty = $orderItem->getQtyDispatched();
        $remainingQty = $purchasedQty - $dispatchedQty;

        return $shipmentItem->getQty() <= $remainingQty;
    }
}
