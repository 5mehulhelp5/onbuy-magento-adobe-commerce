<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order\Sync;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Site $site
    ): Processor {
        return $this->objectManager->create(
            Processor::class,
            ['site' => $site],
        );
    }
}
