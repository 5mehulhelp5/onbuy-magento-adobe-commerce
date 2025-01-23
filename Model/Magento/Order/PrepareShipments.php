<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Order;

class PrepareShipments implements \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentsInterface
{
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory
     */
    private \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory;
    /** @var \M2E\OnBuy\Model\Magento\Order\Shipment\DocumentFactory */
    private Shipment\DocumentFactory $shipmentDocumentFactory;
    /**
     * @var \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentItems
     */
    private Shipment\PrepareShipmentItems $prepareShipmentItems;

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory,
        \M2E\OnBuy\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentItems $prepareShipmentItems
    ) {
        $this->itemCreationFactory = $itemCreationFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->prepareShipmentItems = $prepareShipmentItems;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param \M2E\OnBuy\Model\Order $onBuyOrder
     * @param \Magento\Sales\Model\Order\Item[] $itemsToShip
     *
     * @return array|\Magento\Sales\Model\Order\Shipment[]
     */
    public function prepareShipments(
        \Magento\Sales\Model\Order $magentoOrder,
        \M2E\OnBuy\Model\Order $onBuyOrder,
        array $itemsToShip
    ): array {
        $items = [];
        $itemsQtyToShip = $this->prepareShipmentItems->getQtyToShip($onBuyOrder, $itemsToShip);
        foreach ($itemsToShip as $magentoOrderItem) {
            $qtyToShip = 0;
            if (isset($itemsQtyToShip[$magentoOrderItem->getProductId()])) {
                $qtyToShip = $itemsQtyToShip[$magentoOrderItem->getProductId()];
            }

            if ($qtyToShip === 0) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItem
             */
            $shipmentItem = $this->itemCreationFactory->create();
            $shipmentItem->setQty($qtyToShip);
            $shipmentItem->setOrderItemId($magentoOrderItem->getId());

            $items[$magentoOrderItem->getId()] = $shipmentItem;
        }
        // todo check track

        if (empty($items)) {
            return [];
        }

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentDocumentFactory->create($magentoOrder, $items);
        $shipment->register();

        return [$shipment];
    }
}
