<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Policy\Synchronization;

use M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\Collection as SynchronizationCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SynchronizationCollection
    {
        return $this->objectManager->create(SynchronizationCollection::class);
    }
}
