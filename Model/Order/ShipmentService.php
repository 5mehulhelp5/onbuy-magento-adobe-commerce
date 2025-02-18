<?php

namespace M2E\OnBuy\Model\Order;

class ShipmentService
{
    public const HANDLE_RESULT_FAILED = -1;
    public const HANDLE_RESULT_SKIPPED = 0;
    public const HANDLE_RESULT_SUCCEEDED = 1;

    private \M2E\OnBuy\Model\Order\Shipment\TrackingDetailsBuilder $trackingDetailsBuilder;
    private \M2E\OnBuy\Model\Order\Shipment\ItemLoader $itemLoader;
    private \M2E\OnBuy\Model\Order\Change\Repository $orderChangeRepository;
    private \M2E\OnBuy\Model\Order\ChangeCreateService $orderChangeCreateService;
    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository,
        \M2E\OnBuy\Model\Order\Shipment\TrackingDetailsBuilder $trackingDetailsBuilder,
        \M2E\OnBuy\Model\Order\Shipment\ItemLoader $itemLoader,
        \M2E\OnBuy\Model\Order\Change\Repository $orderChangeRepository,
        \M2E\OnBuy\Model\Order\ChangeCreateService $orderChangeCreateService
    ) {
        $this->trackingDetailsBuilder = $trackingDetailsBuilder;
        $this->itemLoader = $itemLoader;
        $this->orderChangeRepository = $orderChangeRepository;
        $this->orderChangeCreateService = $orderChangeCreateService;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function shipByShipment(
        \M2E\OnBuy\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment,
        int $initiator
    ): int {
        $order->getLogService()->setInitiator($initiator);

        if (!$order->canUpdateShippingStatus()) {
            $order->addErrorLog(
                strtr(
                    "Shipping details could not be sent to the Channel. " .
                    "Reason: Order status on channel_title is already marked as 'Shipped'.",
                    [
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ]
                )
            );

            return self::HANDLE_RESULT_SKIPPED;
        }

        $trackingDetails = $this->trackingDetailsBuilder->build($shipment, $order->getStoreId());
        if ($trackingDetails === null) {
            $order->addErrorLog(
                "Shipping details could not be sent to the Channel. " .
                "Reason: Magento Shipping doesn't have Tracking number."
            );

            return self::HANDLE_RESULT_FAILED;
        }

        $existOrderChange = $this->findExistOrderChange($order, $trackingDetails);
        $orderItemsToShip = $this->itemLoader->loadItemsByShipment($order, $shipment, $existOrderChange);
        if (empty($orderItemsToShip)) {
            $order->addErrorLog(
                "Shipping details could not be sent to the Channel. " .
                "Reason: The order Items have either already been shipped or are not included in this order."
            );

            $this->removeExistOrderChange($order, $existOrderChange);

            return self::HANDLE_RESULT_FAILED;
        }

        $orderChange = $this->createOrderChange(
            $order,
            $orderItemsToShip,
            $trackingDetails,
            $initiator,
            $existOrderChange
        );

        $this->writeTrackingNumberAddedLog($order, $trackingDetails);

        return self::HANDLE_RESULT_SUCCEEDED;
    }

    private function findExistOrderChange(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Order\Shipment\Data\TrackingDetails $trackingDetails
    ): ?\M2E\OnBuy\Model\Order\Change {
        $existChanges = $this->orderChangeRepository->findShippingNotStarted((int)$order->getId());
        foreach ($existChanges as $existChange) {
            $changeParams = $existChange->getParams();

            if (!isset($changeParams['magento_shipment_id'])) {
                continue;
            }

            if ($changeParams['magento_shipment_id'] !== $trackingDetails->getMagentoShipmentId()) {
                continue;
            }

            return $existChange;
        }

        return null;
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Item[] $itemsToShip
     */
    private function createOrderChange(
        \M2E\OnBuy\Model\Order $order,
        array $itemsToShip,
        \M2E\OnBuy\Model\Order\Shipment\Data\TrackingDetails $trackingDetails,
        int $initiator,
        ?\M2E\OnBuy\Model\Order\Change $existOrderChange
    ): \M2E\OnBuy\Model\Order\Change {
        $shipmentItemsQty = $trackingDetails->getShipmentItemsQty();
        $params = [
            'magento_shipment_id' => $trackingDetails->getMagentoShipmentId(),
            'tracking_number' => $trackingDetails->getTrackingNumber(),
            'tracking_title' => $trackingDetails->getCarrierName(),
            'items' => array_map(function ($item) use ($shipmentItemsQty) {
                return [
                    'item_id' => $item->getId(),
                    'qty' => $shipmentItemsQty[$item->getMagentoProductId()],
                ];
            }, $itemsToShip),
        ];

        if ($existOrderChange !== null) {
            $existOrderChange->setParams($params);

            $this->orderChangeRepository->save($existOrderChange);

            return $existOrderChange;
        }

        return $this->orderChangeCreateService->create(
            (int)$order->getId(),
            \M2E\OnBuy\Model\Order\Change::ACTION_UPDATE_SHIPPING,
            $initiator,
            $params,
        );
    }

    private function writeTrackingNumberAddedLog(
        \M2E\OnBuy\Model\Order $order,
        Shipment\Data\TrackingDetails $trackingDetails
    ): void {
        $order->addInfoLog(
            'Tracking number "%tracking_number%" for "%carrier_name%" was added to the Shipment.',
            [
                '!tracking_number' => $trackingDetails->getTrackingNumber(),
                '!carrier_name' => $trackingDetails->getCarrierName(),
            ]
        );
    }

    private function removeExistOrderChange(\M2E\OnBuy\Model\Order $order, ?Change $existOrderChange): void
    {
        if ($existOrderChange === null) {
            return;
        }

        foreach ($existOrderChange->getOrderItemsIdsForShipping() as $id) {
            $item = $order->getItem($id);
            $item->setShippingInProgressNo();

            $this->orderItemRepository->save($item);
        }

        $this->orderChangeRepository->delete($existOrderChange);
    }
}
