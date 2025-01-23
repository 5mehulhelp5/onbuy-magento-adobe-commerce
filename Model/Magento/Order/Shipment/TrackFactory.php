<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Order\Shipment;

class TrackFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\Magento\Sales\Model\Order $magentoOrder, array $trackingDetails): Track
    {
        return $this->objectManager->create(Track::class, [
            'magentoOrder' => $magentoOrder,
            'trackingDetails' => $trackingDetails
        ]);
    }
}
