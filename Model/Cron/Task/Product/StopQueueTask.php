<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Product;

class StopQueueTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'listing/product/stop_queue';

    protected int $intervalInSeconds = 3600;

    private const MAX_PROCESSED_LIFETIME_HOURS_INTERVAL = 720; // 30 days

    private \M2E\OnBuy\Model\StopQueueService $stopQueueService;

    public function __construct(
        \M2E\OnBuy\Model\StopQueueService $stopQueueService,
        \M2E\OnBuy\Model\Cron\Manager $cronManager,
        \M2E\OnBuy\Model\Synchronization\LogService $syncLogger,
        \M2E\OnBuy\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\OnBuy\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\OnBuy\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource,
        );

        $this->stopQueueService = $stopQueueService;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
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
