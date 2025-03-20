<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task;

class InventorySyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'inventory/sync';

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\InventorySync\InitializeFactory $syncInitiatorFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\InventorySync\InitializeFactory $syncInitiatorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->syncInitiatorFactory = $syncInitiatorFactory;
    }

    /**
     * @param \M2E\OnBuy\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        // ----------------------------------------
        foreach ($this->accountRepository->findWithEnabledInventorySync() as $account) {
            foreach ($account->getSites() as $site) {
                try {
                    $context->getOperationHistory()->addText(
                        "Starting Account (Site) '{$account->getTitle()} ({$site->getName()})'",
                    );

                    $syncInitiator = $this->syncInitiatorFactory->create($account, $site);
                    if (!$syncInitiator->isAllowed()) {
                        $context->getOperationHistory()->addText(
                            "Skipped Account (Site) '{$account->getTitle()} ({$site->getName()})'",
                        );

                        continue;
                    }

                    $context->getOperationHistory()->addTimePoint(
                        $account->getId() . $site->getSiteId(),
                        "Process Account '{$account->getTitle()} ({$site->getName()})'",
                    );

                    // ----------------------------------------

                    $syncInitiator->process();
                } catch (\Throwable $e) {
                    $context->getOperationHistory()->addText(
                        "Error '{$account->getTitle()} ({$site->getSiteId()})'. Message: {$e->getMessage()}",
                    );
                }

                // ----------------------------------------

                $context->getOperationHistory()->saveTimePoint($account->getId() . $site->getSiteId());
            }
        }
    }
}
