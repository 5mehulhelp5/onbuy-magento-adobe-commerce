<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\MSI\Magento\Order;

use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface as DefaultAlgorithm;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;

class PrepareShipments implements \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentsInterface
{
    private \M2E\OnBuy\Helper\Magento\Product $magentoProductHelper;
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory
     */
    private \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory;
    private \M2E\OnBuy\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory;
    private \Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface $sourceSelectionService;
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory
     */
    private \Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory $itemRequestFactory;
    private \Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver;
    private \Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface $algorithm;
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory
     */
    private \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory $inventoryRequestFactory;
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Sales\Api\Data\ShipmentExtensionFactory
     */
    private \Magento\Sales\Api\Data\ShipmentExtensionFactory $shipmentExtensionFactory;
    private \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentItems $prepareShipmentItems;

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \M2E\OnBuy\Helper\Magento\Product $magentoProductHelper,
        \M2E\OnBuy\Model\Magento\Order\Shipment\PrepareShipmentItems $prepareShipmentItems,
        \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory,
        \M2E\OnBuy\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->magentoProductHelper = $magentoProductHelper;
        $this->prepareShipmentItems = $prepareShipmentItems;
        $this->itemCreationFactory = $itemCreationFactory;
        $this->itemRequestFactory = $objectManager->get(ItemRequestInterfaceFactory::class);
        $this->inventoryRequestFactory = $objectManager->get(InventoryRequestInterfaceFactory::class);
        $this->stockByWebsiteIdResolver = $objectManager->get(StockByWebsiteIdResolverInterface::class);
        $this->algorithm = $objectManager->get(DefaultAlgorithm::class);
        $this->sourceSelectionService = $objectManager->get(SourceSelectionServiceInterface::class);
        $this->shipmentExtensionFactory = $objectManager->get(ShipmentExtensionFactory::class);
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param \M2E\OnBuy\Model\Order $onBuyOrder
     * @param array $itemsToShip
     *
     * @return \Magento\Sales\Model\Order\Shipment[]
     */
    public function prepareShipments(
        \Magento\Sales\Model\Order $magentoOrder,
        \M2E\OnBuy\Model\Order $onBuyOrder,
        array $itemsToShip
    ): array {
        $selectionRequestItems = [];
        $orderItemIdsBySku = [];

        $itemsQtyToShip = $this->prepareShipmentItems->getQtyToShip($onBuyOrder, $itemsToShip);

        foreach ($itemsToShip as $item) {
            $qtyToShip = $itemsQtyToShip[$item->getProductId()];
            if (empty($qtyToShip)) {
                continue;
            }

            /**
             * Magento interface do not support situation when a bundle product
             * with the parameter "Ship Bundle Items" == "Together" is in one order with products
             * with more then 1 Source
             */
            if (
                $this->magentoProductHelper->isBundleType($item->getProductType())
                && !$item->isShipSeparately()
            ) {
                throw new \M2E\OnBuy\Model\Exception\Logic(
                    'Shipping Bundle items together is not supported by Magento in Multi Source mode.'
                );
            }

            /** @psalm-suppress UndefinedClass */
            $selectionRequestItems[] = $this->itemRequestFactory->create([
                'sku' => $item->getSku(),
                'qty' => $qtyToShip,
            ]);

            $orderItemIdsBySku[$item->getSku()] = $item->getItemId();
        }

        if (
            empty($selectionRequestItems)
            || empty($orderItemIdsBySku)
        ) {
            return [];
        }

        $websiteId = (int)$magentoOrder->getStore()->getWebsiteId();

        /**
         * @psalm-suppress UndefinedClass
         * @var \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface $inventoryRequest
         */
        $inventoryRequest = $this->inventoryRequestFactory->create(
            [
                'stockId' => $this->stockByWebsiteIdResolver->execute($websiteId)->getStockId(),
                'items' => $selectionRequestItems,
            ]
        );

        $selectionAlgorithmCode = $this->algorithm->execute();
        $sourceSelectionResult = $this->sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);

        $itemsPerSourceCode = [];

        foreach ($sourceSelectionResult->getSourceSelectionItems() as $sourceSelectionItem) {
            if ($sourceSelectionItem->getQtyToDeduct() <= 0) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItem
             */
            $shipmentItem = $this->itemCreationFactory->create();
            $shipmentItem->setQty($sourceSelectionItem->getQtyToDeduct());
            $shipmentItem->setOrderItemId($orderItemIdsBySku[$sourceSelectionItem->getSku()]);
            $itemsPerSourceCode[$sourceSelectionItem->getSourceCode()][] = $shipmentItem;
        }

        $shipments = [];
        /**
         * The track number of only one, last shipment is sent to Channel.
         * When creating more then one shipments for one order, problems may arise.
         */
        foreach ($itemsPerSourceCode as $sourceCode => $shipmentItems) {
            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Sales\Model\Order\Shipment $shipment
             */
            $shipment = $this->shipmentDocumentFactory->create($magentoOrder, $shipmentItems);
            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Sales\Api\Data\ShipmentExtensionInterface $shipmentExtension
             */
            $shipmentExtension = $this->shipmentExtensionFactory->create();
            /** @psalm-suppress UndefinedDocblockClass */
            $shipmentExtension->setSourceCode((string)$sourceCode);
            $shipment->setExtensionAttributes($shipmentExtension);
            $shipment->register();

            $shipments[] = $shipment;
        }

        return $shipments;
    }
}
