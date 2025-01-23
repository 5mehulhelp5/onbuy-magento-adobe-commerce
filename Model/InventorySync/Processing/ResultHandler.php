<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync\Processing;

class ResultHandler implements \M2E\OnBuy\Model\Processing\PartialResultHandlerInterface
{
    public const NICK = 'inventory_sync';

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;
    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;
    private \M2E\OnBuy\Model\UnmanagedProduct\UpdateFromChannelFactory $unmanagedProductUpdateFromChannelProcessorFactory;
    private \M2E\OnBuy\Model\InventorySync\ProductBuilderFactory $channelProductCollectionBuilderFactory;
    private \M2E\OnBuy\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor;
    private \DateTime $fromDate;
    private \M2E\OnBuy\Model\InventorySync\ReceivedProduct\Processor $receivedProductProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\UpdateFromChannelFactory $unmanagedProductUpdateFromChannelProcessorFactory,
        \M2E\OnBuy\Model\InventorySync\ProductBuilderFactory $channelProductCollectionBuilderFactory,
        \M2E\OnBuy\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor,
        \M2E\OnBuy\Model\InventorySync\ReceivedProduct\Processor $receivedProductProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->siteRepository = $siteRepository;
        $this->unmanagedProductUpdateFromChannelProcessorFactory = $unmanagedProductUpdateFromChannelProcessorFactory;
        $this->channelProductCollectionBuilderFactory = $channelProductCollectionBuilderFactory;
        $this->productUpdateFromChannelProcessor = $productUpdateFromChannelProcessor;
        $this->receivedProductProcessor = $receivedProductProcessor;
    }

    public function initialize(array $params): void
    {
        if (!isset($params['account_id'], $params['site_id'])) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Processing params is not valid.');
        }

        $account = $this->accountRepository->find($params['account_id']);
        if ($account === null) {
            throw new \M2E\OnBuy\Model\Exception('Account not found');
        }

        $this->account = $account;
        $site = null;
        foreach ($this->account->getSites() as $accountSite) {
            if ($accountSite->getId() === $params['site_id']) {
                $site = $accountSite;
                break;
            }
        }

        if ($site === null) {
            throw new \M2E\OnBuy\Model\Exception('Site not found');
        }

        $this->site = $site;

        if (isset($params['current_date'])) {
            $this->fromDate = \M2E\Core\Helper\Date::createDateGmt($params['current_date']);
        }
    }

    public function processPartialResult(array $partialData): void
    {
        $channelProductBuilder = $this->channelProductCollectionBuilderFactory->create(
            $this->account,
            $this->site
        );
        $channelProductCollection = $channelProductBuilder->build($partialData);

        // ----------------------------------------

        $this->receivedProductProcessor->collectReceivedProducts(
            $this->account,
            $this->site,
            $channelProductCollection
        );

        // ----------------------------------------

        $productCollection = $this->unmanagedProductUpdateFromChannelProcessorFactory
            ->create($this->account, $this->site)
            ->process(clone $channelProductCollection);

        if ($productCollection !== null) {
            $this->productUpdateFromChannelProcessor
                ->process($productCollection, $this->account, $this->site);
        }
    }

    public function processSuccess(array $resultData, array $messages): void
    {
        $this->site->setInventoryLastSyncDate(clone $this->fromDate);

        $this->siteRepository->save($this->site);

        $this->receivedProductProcessor->processDeletedProducts($this->account, $this->site, clone $this->fromDate);
    }

    public function processExpire(): void
    {
        // do nothing
    }

    public function clearLock(\M2E\OnBuy\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->delete(\M2E\OnBuy\Model\Site::LOCK_NICK, $this->site->getId());
    }
}
