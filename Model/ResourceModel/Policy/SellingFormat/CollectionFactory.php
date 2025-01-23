<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat;

use M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat\Collection as PolicySellingFormatCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): PolicySellingFormatCollection
    {
        return $this->objectManager->create(PolicySellingFormatCollection::class);
    }
}
