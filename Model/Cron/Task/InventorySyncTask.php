<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task;

class InventorySyncTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'inventory/sync';

    protected int $intervalInSeconds = 300;

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\InventorySync\InitializeFactory $syncInitiatorFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\InventorySync\InitializeFactory $syncInitiatorFactory,
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
        $this->accountRepository = $accountRepository;
        $this->syncInitiatorFactory = $syncInitiatorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\OnBuy\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $synchronizationLog->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        foreach ($this->accountRepository->findWithEnabledInventorySync() as $account) {
            foreach ($account->getSites() as $site) {
                try {
                    $this->getOperationHistory()->addText(
                        "Starting Account (Site) '{$account->getTitle()} ({$site->getName()})'",
                    );

                    $syncInitiator = $this->syncInitiatorFactory->create($account, $site);
                    if (!$syncInitiator->isAllowed()) {
                        $this->getOperationHistory()->addText(
                            "Skipped Account (Site) '{$account->getTitle()} ({$site->getName()})'",
                        );

                        continue;
                    }

                    $this->getOperationHistory()->addTimePoint(
                        $account->getId() . $site->getSiteId(),
                        "Process Account '{$account->getTitle()} ({$site->getName()})'",
                    );

                    // ----------------------------------------

                    $syncInitiator->process();
                } catch (\Throwable $e) {
                    $this->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$site->getSiteId()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $this->getOperationHistory()->saveTimePoint($account->getId() . $site->getSiteId());
            }
        }
    }
}
