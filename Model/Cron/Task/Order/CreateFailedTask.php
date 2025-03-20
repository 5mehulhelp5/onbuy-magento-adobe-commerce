<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class CreateFailedTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/create_failed';

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;
    private \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Order\Repository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->orderMagentoProcessor = $orderMagentoProcessor;
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
            try {
                $borderDate = \M2E\Core\Helper\Date::createCurrentGmt();
                $borderDate->modify('-15 minutes');

                $orders = $this->orderRepository->findForAttemptMagentoCreate(
                    $account,
                    $borderDate,
                    \M2E\OnBuy\Model\Order::MAGENTO_ORDER_CREATE_MAX_TRIES,
                    20
                );

                $this->orderMagentoProcessor->processBatch(
                    $orders,
                    \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                    true,
                    true
                );
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Create Failed Orders" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }
}
