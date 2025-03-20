<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class SyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/sync';

    private Sync\ProcessorFactory $ordersProcessorFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        Sync\ProcessorFactory $ordersProcessorFactory
    ) {
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param \M2E\OnBuy\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            foreach ($account->getSites() as $site) {
                try {
                    $ordersProcessor = $this->ordersProcessorFactory->create($site);
                    $ordersProcessor->process();
                } catch (\Throwable $e) {
                    $context->getExceptionHandler()->processTaskException($e);
                }
            }
        }
    }
}
