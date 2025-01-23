<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync;

class Initialize
{
    private const SYNC_INTERVAL_8_HOURS_IN_SECONDS = 28800;

    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;

    /** @var \M2E\OnBuy\Model\InventorySync\LockManager */
    private LockManager $lockManager;
    private \M2E\OnBuy\Model\Processing\Runner $processingRunner;
    /** @var \M2E\OnBuy\Model\InventorySync\Processing\InitiatorFactory */
    private Processing\InitiatorFactory $processingInitiatorFactory;
    /** @var \M2E\OnBuy\Model\InventorySync\ReceivedProduct\Processor */
    private ReceivedProduct\Processor $receivedProductProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\InventorySync\LockManager $lockManager,
        \M2E\OnBuy\Model\Processing\Runner $processingRunner,
        \M2E\OnBuy\Model\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\OnBuy\Model\InventorySync\ReceivedProduct\Processor $receivedProductProcessor
    ) {
        $this->account = $account;
        $this->site = $site;
        $this->lockManager = $lockManager;
        $this->processingRunner = $processingRunner;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
        $this->receivedProductProcessor = $receivedProductProcessor;
    }

    public function isAllowed(): bool
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        $lastSyncDate = $this->site->getInventoryLastSyncDate();
        if (
            $lastSyncDate !== null
            && $lastSyncDate->modify('+ ' . self::SYNC_INTERVAL_8_HOURS_IN_SECONDS . ' seconds') > $currentDate
        ) {
            return false;
        }

        return !$this->lockManager->isExistBySite($this->site);
    }

    public function process(): void
    {
        if (!$this->isAllowed()) {
            return;
        }

        $this->receivedProductProcessor->clear($this->account, $this->site);

        $initiator = $this->processingInitiatorFactory->create($this->account, $this->site);

        $this->processingRunner->run($initiator);
    }
}
