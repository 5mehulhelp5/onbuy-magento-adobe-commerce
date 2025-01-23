<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class SyncTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'order/sync';

    /** @var int in seconds */
    protected int $intervalInSeconds = 300;

    private Sync\ProcessorFactory $ordersProcessorFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        \M2E\OnBuy\Model\Cron\Manager $cronManager,
        Sync\ProcessorFactory $ordersProcessorFactory,
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
        $this->ordersProcessorFactory = $ordersProcessorFactory;
        $this->accountRepository = $accountRepository;
        $this->exceptionHelper = $exceptionHelper;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $synchronizationLog = $this->getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            foreach ($account->getSites() as $site) {
                try {
                    $ordersProcessor = $this->ordersProcessorFactory->create($site);
                    $ordersProcessor->process();
                } catch (\Throwable $e) {
                    $this->exceptionHelper->process($e);
                    $synchronizationLog->addFromException($e);
                }
            }
        }
    }
}
