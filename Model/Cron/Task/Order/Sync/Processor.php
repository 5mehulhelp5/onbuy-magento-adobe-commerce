<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Order\Sync;

use M2E\OnBuy\Model\Channel\Order\RetrieveProcessor as ItemsByUpdateDateProcessor;

class Processor
{
    private \M2E\OnBuy\Model\Synchronization\LogService $synchronizationLog;
    private ItemsByUpdateDateProcessor $receiveOrdersProcessor;
    private \M2E\OnBuy\Model\Site $site;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;
    private \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor;
    private \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory;

    public function __construct(
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Order\MagentoProcessor $orderMagentoProcessor,
        \M2E\OnBuy\Model\Synchronization\LogService $logService,
        ItemsByUpdateDateProcessor $receiveOrdersProcessor,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\Order\UpdateFromChannelFactory $updateFromChannelFactory
    ) {
        $this->receiveOrdersProcessor = $receiveOrdersProcessor;
        $this->synchronizationLog = $logService;
        $this->site = $site;
        $this->siteRepository = $siteRepository;
        $this->orderMagentoProcessor = $orderMagentoProcessor;
        $this->updateFromChannelFactory = $updateFromChannelFactory;
    }

    public function process(): void
    {
        $toTime = \M2E\Core\Helper\Date::createImmutableCurrentGmt();
        $fromTime = $this->prepareFromTime($this->site, $toTime);

        $response = $this->receiveOrdersProcessor->process(
            $this->site->getAccount(),
            $this->site,
            $fromTime,
            $toTime
        );

        $this->processResponseMessages($response->getMessageCollection());

        $this->updateLastOrderSynchronizationDate($this->site, $response->getMaxDateInResult());

        if (empty($response->getOrders())) {
            return;
        }

        $ordersCreator = $this->updateFromChannelFactory->create($this->site->getAccount(), $this->site, true);

        $orders = $ordersCreator->process($response->getOrders());

        $this->orderMagentoProcessor->processBatch(
            $orders,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            true,
            true
        );
    }

    // ---------------------------------------

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

            $this->synchronizationLog->add((string)__($message->getText()), $logType);
        }
    }

    private function prepareFromTime(
        \M2E\OnBuy\Model\Site $site,
        \DateTimeImmutable $toTime
    ): \DateTimeImmutable {
        $lastSynchronizationDate = $site->getOrdersLastSyncDate();

        if ($lastSynchronizationDate === null) {
            $sinceTime = \M2E\Core\Helper\Date::createImmutableCurrentGmt();
        } else {
            $sinceTime = $lastSynchronizationDate;

            // Get min date for sync
            // ---------------------------------------
            $minDate = \M2E\Core\Helper\Date::createImmutableCurrentGmt()->modify('-90 days');
            // ---------------------------------------

            // Prepare last date
            // ---------------------------------------
            if ($sinceTime->getTimestamp() < $minDate->getTimestamp()) {
                $sinceTime = $minDate;
            }
        }

        if ($sinceTime->getTimestamp() >= $toTime->getTimeStamp()) {
            $sinceTime = $toTime->modify('- 5 minutes');
        }

        return $sinceTime;
    }

    private function updateLastOrderSynchronizationDate(
        \M2E\OnBuy\Model\Site $site,
        \DateTimeInterface $date
    ): void {
        $site->setOrdersLastSyncDate($date);

        $this->siteRepository->save($site);
    }
}
