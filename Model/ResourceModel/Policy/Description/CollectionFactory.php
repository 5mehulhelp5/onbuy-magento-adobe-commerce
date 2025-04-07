<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Policy\Description;

use M2E\OnBuy\Model\ResourceModel\Policy\Description\Collection as PolicyDescriptionCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): PolicyDescriptionCollection
    {
        return $this->objectManager->create(PolicyDescriptionCollection::class);
    }
}
