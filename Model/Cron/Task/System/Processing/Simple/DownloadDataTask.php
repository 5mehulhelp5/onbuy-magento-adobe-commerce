<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\System\Processing\Simple;

class DownloadDataTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'processing/simple/download/data';

    private \M2E\OnBuy\Model\Processing\RetrieveData\Simple $retrieveDataSimple;

    public function __construct(
        \M2E\OnBuy\Model\Processing\RetrieveData\Simple $retrieveDataSimple,
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
        $this->retrieveDataSimple = $retrieveDataSimple;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->retrieveDataSimple->process();
    }
}
