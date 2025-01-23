<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class CreateFailedTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'order/create_failed';

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Order\Repository $orderRepository;
    private \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
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
            $resource
        );
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->orderMagentoProcessor = $orderMagentoProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\OnBuy\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions()
    {
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
                $message = (string)\__(
                    'The "Create Failed Orders" Action for Account "%1" was completed with error.',
                    $account->getTitle(),
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }
}
