<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class ItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Item
    {
        return $this->objectManager->create(Item::class);
    }

    public function createFromChannel(
        \M2E\OnBuy\Model\Order $order,
        \M2E\OnBuy\Model\Channel\Order\Item $channelItem
    ): Item {
        $obj = $this->createEmpty();

        $obj->create(
            $order,
            $channelItem->getChannelProductId(),
            $channelItem->getSku(),
            $channelItem->getQty()
        );

        $obj->setQtyDispatched($channelItem->getQtyDispatched())
            // ----------------------------------------
            ->setChannelProductTitle($channelItem->getName())
            // ----------------------------------------
            ->setSalePrice($channelItem->getPrice()->unit)
            // ----------------------------------------
            ->setExpectedDispatchDate($channelItem->getExpectedDispatchDate())
            // ----------------------------------------
            ->setFee($channelItem->getFee())
            // ----------------------------------------
            ->setTaxDetails(self::createTaxDetails($channelItem))
            ->setTrackingDetails(self::createTrackingDetails($channelItem->getTracking()));

        return $obj;
    }

    /**
     * @param \M2E\OnBuy\Model\Order\Item $item
     * @param \M2E\OnBuy\Model\Channel\Order\Item $channelItem
     *
     * @return bool - was updated
     */
    public static function updateFromChannel(Item $item, \M2E\OnBuy\Model\Channel\Order\Item $channelItem): bool
    {
        $wasChanged = false;
        if ($item->getQtyDispatched() !== $channelItem->getQtyDispatched()) {
            $item->setQtyDispatched($channelItem->getQtyDispatched());

            $wasChanged = true;
        }

        if ($item->getChannelProductTitle() !== $channelItem->getName()) {
            $item->setChannelProductTitle($channelItem->getName());

            $wasChanged = true;
        }

        if ($item->getSalePrice() !== $channelItem->getPrice()->unit) {
            $item->setSalePrice($channelItem->getPrice()->unit);

            $wasChanged = true;
        }

        if ($item->getExpectedDispatchDate() != $channelItem->getExpectedDispatchDate()) {
            $item->setExpectedDispatchDate($channelItem->getExpectedDispatchDate());
        }

        if ($item->getFee() !== $channelItem->getFee()) {
            $item->setFee($channelItem->getFee());

            $wasChanged = true;
        }

        if ($item->getTaxDetails() !== self::createTaxDetails($channelItem)) {
            $item->setTaxDetails(self::createTaxDetails($channelItem));

            $wasChanged = true;
        }

        if ($item->getTrackingDetails() !== self::createTrackingDetails($channelItem->getTracking())) {
            $item->setTrackingDetails(self::createTrackingDetails($channelItem->getTracking()));

            $wasChanged = true;
        }

        return $wasChanged;
    }

    // ----------------------------------------

    private static function createTaxDetails(\M2E\OnBuy\Model\Channel\Order\Item $channelItem): array
    {
        $tax = $channelItem->getTax();

        $rate = null;
        if ($tax->taxTotal !== null) {
            $rate = self::resolveTax(
                $channelItem->getPrice()->total,
                $tax->taxTotal
            );
        }

        return [
            'rate' => $rate,
            'amount' => $tax->taxTotal,
            'tax_delivery' => $tax->taxDelivery,
            'tax_product' => $tax->taxProduct,
            'tax_scheme' => $tax->taxScheme,
        ];
    }

    /**
     * @return int|float
     */
    private static function resolveTax(float $totalPrice, float $taxTotal)
    {
        return \M2E\OnBuy\Model\Order\TaxResolver::calculateRate($totalPrice, $taxTotal);
    }

    private static function createTrackingDetails(?\M2E\OnBuy\Model\Channel\Order\Item\Tracking $tracking): array
    {
        if ($tracking === null) {
            return [];
        }

        return [
            'supplier_name' => $tracking->supplierName,
            'tracking_number' => $tracking->trackingNumber,
            'tracking_url' => $tracking->trackingUrl,
        ];
    }
}
