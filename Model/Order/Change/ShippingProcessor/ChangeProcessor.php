<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order\Change\ShippingProcessor;

class ChangeProcessor
{
    private \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Processor $sendEntityProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\Processor $sendEntityProcessor
    ) {
        $this->sendEntityProcessor = $sendEntityProcessor;
    }

    public function process(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Order\Change $change
    ): ChangeResult {
        $order = $change->getOrder();
        $changeParams = $change->getParams();

        $trackingNumber = $changeParams['tracking_number'];
        $trackingTitle = $changeParams['tracking_title'];

        $orderBuilder = \M2E\OnBuy\Model\Channel\Connector\Order\Send\Entity\OrderBuilder::create();
        $orderBuilder->setOrderId($order->getChannelOrderId());

        $orderItems = $this->getOrderItemsForShipping($change);

        if (empty($orderItems)) {
            return ChangeResult::createSkipped();
        }

        $orderBuilder->setTrackingInfo($trackingTitle, $trackingNumber);
        $products = $orderBuilder->buildProducts(
            $orderItems,
            $change->getOrderItemsForShipping()
        );
        $orderBuilder->setProducts($products);

        if (empty($orderBuilder->getTrackingInfo())) {
            return ChangeResult::createSkipped();
        }

        $response = $this->sendEntityProcessor->process(
            $account,
            $order->getSite(),
            $orderBuilder->build()
        );

        if (!$response->isSuccess()) {
            return ChangeResult::createFailed(
                $orderItems,
                $trackingNumber,
                $trackingTitle,
                $response->getErrorMessages()
            );
        }

        return ChangeResult::createSuccess(
            $orderItems,
            $trackingNumber,
            $trackingTitle,
            $response->getErrorMessages(),
        );
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Change $change
     *
     * @return \M2E\OnBuy\Model\Order\Item[]
     */
    private function getOrderItemsForShipping(\M2E\OnBuy\Model\Order\Change $change): array
    {
        $order = $change->getOrder();
        $changeParams = $change->getParams();

        $orderItemsForShipping = [];
        foreach ($changeParams['items'] as $orderItemData) {
            $itemId = (int)$orderItemData['item_id'];
            $orderItem = $order->findItem($itemId);

            if ($orderItem === null) {
                continue;
            }

            if (!$this->canItemBeShipped($orderItem, $change->getOrderItemsForShipping())) {
                continue;
            }

            $orderItemsForShipping[] = $orderItem;
        }

        return $orderItemsForShipping;
    }

    private function canItemBeShipped(
        \M2E\OnBuy\Model\Order\Item $orderItem,
        array $itemsForShip
    ): bool {
        $purchasedQty = $orderItem->getQtyPurchased();
        $dispatchedQty = $orderItem->getQtyDispatched();
        $remainingQty = $purchasedQty - $dispatchedQty;

        foreach ($itemsForShip as $itemForShip) {
            if ((int)$itemForShip['item_id'] === (int)$orderItem->getId()) {
                return (int)$itemForShip['qty'] <= $remainingQty;
            }
        }

        return false;
    }
}
