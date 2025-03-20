<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class ProcessChangesTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/process_changes';

    private \M2E\OnBuy\Model\Order\Change\Repository $orderChangeRepository;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Order\Change\ShippingProcessor $shippingProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Order\Change\ShippingProcessor $shippingProcessor,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Order\Change\Repository $orderChangeRepository
    ) {
        $this->orderChangeRepository = $orderChangeRepository;
        $this->accountRepository = $accountRepository;
        $this->shippingProcessor = $shippingProcessor;
    }

    /**
     * @param \M2E\OnBuy\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_ORDERS);

        $this->deleteNotActualChanges();

        $accounts = $this->accountRepository->getAll();
        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->shippingProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    // ----------------------------------------

    private function deleteNotActualChanges(): void
    {
        $this->orderChangeRepository->deleteByProcessingAttemptCount(
            \M2E\OnBuy\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS
        );
    }
}
