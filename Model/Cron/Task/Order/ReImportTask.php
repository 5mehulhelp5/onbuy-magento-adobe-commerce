<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order;

class ReImportTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/re_import';

    private \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $reimportManager;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Channel\Order\RetrieveProcessor $receiveOrderProcessor;
    private \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor;
    private \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory;
    private \M2E\OnBuy\Model\Synchronization\LogService $syncLog;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Channel\Order\RetrieveProcessor $receiveOrderProcessor,
        \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $reimportManager,
        \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor,
        \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory
    ) {
        $this->reimportManager = $reimportManager;
        $this->accountRepository = $accountRepository;
        $this->receiveOrderProcessor = $receiveOrderProcessor;
        $this->orderMagentoProcessor = $orderMagentoProcessor;
        $this->updateFromChannelFactory = $updateFromChannelFactory;
    }

    /**
     * @param \M2E\OnBuy\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $this->syncLog = $context->getSynchronizationLog();
        $this->syncLog->setTask(\M2E\OnBuy\Model\Synchronization\Log::TASK_ORDERS);

        foreach ($this->accountRepository->getAll() as $account) {
            try {
                foreach ($account->getSites() as $site) {
                    $manager = $this->reimportManager->create($account, $site);
                    if (!$manager->isEnabled()) {
                        continue;
                    }

                    /** @var \DateTimeImmutable $toTime */
                    $toTime = $manager->getToDate();

                    /** @var \DateTimeImmutable $fromTime */
                    $fromTime = $manager->getCurrentFromDate() ?? $manager->getFromDate();

                    $response = $this->receiveOrderProcessor->process(
                        $account,
                        $site,
                        $fromTime,
                        $toTime,
                    );

                    $this->processResponseMessages($response->getMessageCollection());

                    $maxDateInResult = $response->getMaxDateInResult();

                    $this->handleToDate($manager, $maxDateInResult);

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
            } catch (\Throwable $exception) {
                $message = (string)\__(
                    'The "Upload Orders By User" Action for %channel_title Account "%account" was completed with error.',
                    [
                        'account' => $account->getTitle(),
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ],
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
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

            $this->syncLog
                ->add((string)__($message->getText()), $logType);
        }
    }

    private function handleToDate(
        \M2E\OnBuy\Model\Order\ReImport\Manager $manager,
        \DateTimeImmutable $maxDateInResult
    ): void {
        if ($maxDateInResult->getTimestamp() >= $manager->getToDate()->getTimestamp()) {
            $manager->clear();
        } else {
            $manager->setCurrentFromDate($maxDateInResult);
        }
    }
}
