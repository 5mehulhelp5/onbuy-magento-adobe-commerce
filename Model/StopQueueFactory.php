<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class StopQueueFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): StopQueue
    {
        return $this->objectManager->create(StopQueue::class);
    }

    public function create(int $accountId, int $siteId, string $onlineSku): StopQueue
    {
        $obj = $this->createEmpty();

        $obj->create($accountId, $siteId, $onlineSku);

        return $obj;
    }
}
