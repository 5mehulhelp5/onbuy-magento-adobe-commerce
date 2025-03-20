<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Product;

class StopQueueTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'listing/product/stop_queue';
    private const MAX_PROCESSED_LIFETIME_HOURS_INTERVAL = 720; // 30 days

    private \M2E\OnBuy\Model\StopQueueService $stopQueueService;

    public function __construct(
        \M2E\OnBuy\Model\StopQueueService $stopQueueService
    ) {
        $this->stopQueueService = $stopQueueService;
    }

    public function process($context): void
    {
        $this->deleteOldProcessedItems();

        $this->processItems();
    }

    private function deleteOldProcessedItems(): void
    {
        $borderDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $borderDate->modify('- ' . self::MAX_PROCESSED_LIFETIME_HOURS_INTERVAL . ' hours');

        $this->stopQueueService->deleteOldProcessed($borderDate);
    }

    private function processItems(): void
    {
        $this->stopQueueService->process();
    }
}
