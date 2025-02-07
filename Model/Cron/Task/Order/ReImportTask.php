<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class ReImportTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'order/re_import';

    private \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $reimportManager;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Channel\Order\RetrieveProcessor $receiveOrderProcessor;
    private \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor;
    private \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Channel\Order\RetrieveProcessor $receiveOrderProcessor,
        \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $reimportManager,
        \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor,
        \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory,
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
        $this->reimportManager = $reimportManager;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
        $this->orderMagentoProcessor = $orderMagentoProcessor;
        $this->updateFromChannelFactory = $updateFromChannelFactory;
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

    protected function performActions(): void
    {
        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->reimportManager->create($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $minToDate = null;

                /** @var \DateTimeImmutable $fromTime */
                $fromTime = $manager->getCurrentFromDate() ?? $manager->getFromDate();

                /** @var \DateTimeImmutable $toTime */
                $toTime = $manager->getToDate();
                foreach ($account->getSites() as $site) {
                    $response = $this->receiveOrderProcessor->process(
                        $account,
                        $site,
                        $fromTime,
                        $toTime,
                    );

                    $this->processResponseMessages($response->getMessageCollection());

                    if ($minToDate === null) {
                        $minToDate = $response->getMaxDateInResult();
                    } elseif ($minToDate->getTimestamp() > $response->getMaxDateInResult()->getTimestamp()) {
                        $minToDate = $response->getMaxDateInResult();
                    }

                    if (empty($response->getOrders())) {
                        continue;
                    }

                    $ordersCreator = $this->updateFromChannelFactory->create($account, $site, false);

                    $orders = $ordersCreator->process($response->getOrders());
                    $this->orderMagentoProcessor->processBatch(
                        $orders,
                        \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                        true,
                        true
                    );
                }

                $this->handleToDate($manager, $minToDate);
            } catch (\Throwable $exception) {
                $message = (string)\__(
                    'The "Upload Orders By User" Action for OnBuy Account "%account" was completed with error.',
                    ['account' => $account->getTitle()],
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    private function processResponseMessages(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): void {
        foreach ($messageCollection->getMessages() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError()
                ? \M2E\OnBuy\Model\Log\AbstractModel::TYPE_ERROR
                : \M2E\OnBuy\Model\Log\AbstractModel::TYPE_WARNING;

            $this
                ->getSynchronizationLog()
                ->add((string)__($message->getText()), $logType);
        }
    }

    private function handleToDate(
        \M2E\OnBuy\Model\Order\ReImport\Manager $manager,
        \DateTimeImmutable $toDate
    ): void {
        if ($toDate->getTimestamp() >= $manager->getToDate()->getTimestamp()) {
            $manager->clear();

            return;
        }

        $manager->setCurrentFromDate($toDate);
    }
}
