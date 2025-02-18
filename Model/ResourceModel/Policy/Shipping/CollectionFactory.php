<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Policy\Shipping;

use M2E\OnBuy\Model\ResourceModel\Policy\Shipping\Collection as ShippingCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ShippingCollection
    {
        return $this->objectManager->create(ShippingCollection::class);
    }
}
