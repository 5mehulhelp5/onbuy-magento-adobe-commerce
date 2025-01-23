<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task;

class ProcessScheduledActionsTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'scheduled_actions/process';

    private \M2E\OnBuy\Model\ScheduledAction\Processor $processor;

    public function __construct(
        \M2E\OnBuy\Model\Cron\Manager $cronManager,
        \M2E\OnBuy\Model\Synchronization\LogService $syncLogger,
        \M2E\OnBuy\Model\ScheduledAction\Processor $processor,
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
            $resource
        );

        $this->processor = $processor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->processor->process();
    }
}
