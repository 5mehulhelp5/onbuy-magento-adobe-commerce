<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Account;

use M2E\OnBuy\Model\ResourceModel\Account as AccountResource;

class Repository
{
    use \M2E\Core\Model\Repository\CacheTrait;

    private \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $collectionFactory;
    private \M2E\OnBuy\Model\AccountFactory $accountFactory;
    private \M2E\OnBuy\Model\ResourceModel\Account $accountResource;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\OnBuy\Model\AccountFactory $accountFactory,
        \M2E\OnBuy\Model\ResourceModel\Account $accountResource,
        \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->accountFactory = $accountFactory;
        $this->accountResource = $accountResource;
        $this->cache = $cache;
    }

    public function create(\M2E\OnBuy\Model\Account $account): void
    {
        $this->accountResource->save($account);
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Account
    {
        $account = $this->accountFactory->createEmpty();

        $cacheData = $this->cache->getValue($this->makeCacheKey($account, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($account, $cacheData);

            return $account;
        }

        $this->accountResource->load($account, $id);

        if ($account->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($account, $id),
            $this->getCacheDate($account),
            [],
            60 * 60
        );

        return $account;
    }

    public function get(int $id): \M2E\OnBuy\Model\Account
    {
        $account = $this->find($id);
        if ($account === null) {
            throw new \LogicException("Account '$id' not found.");
        }

        return $account;
    }

    public function save(\M2E\OnBuy\Model\Account $account): void
    {
        $this->accountResource->save($account);
        $this->cache->removeValue($this->makeCacheKey($account, $account->getId()));
    }

    public function remove(\M2E\OnBuy\Model\Account $account): void
    {
        $this->cache->removeValue($this->makeCacheKey($account, $account->getId()));

        $this->accountResource->delete($account);
    }

    /**
     * @return \M2E\OnBuy\Model\Account[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function findFirst(): ?\M2E\OnBuy\Model\Account
    {
        $collection = $this->collectionFactory->create();
        $firstAccount = $collection->getFirstItem();
        if ($firstAccount->isObjectNew()) {
            return null;
        }

        return $firstAccount;
    }

    public function getFirst(): \M2E\OnBuy\Model\Account
    {
        $firstAccount = $this->findFirst();
        if ($firstAccount === null) {
            throw new \LogicException('Not found any accounts');
        }

        return $firstAccount;
    }

    public function findByIdentifier(string $identifier): ?\M2E\OnBuy\Model\Account
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(AccountResource::COLUMN_IDENTIFIER, $identifier);

        $account = $collection->getFirstItem();
        if ($account->isObjectNew()) {
            return null;
        }

        return $account;
    }

    /**
     * @return \M2E\OnBuy\Model\Account[]
     */
    public function findWithEnabledInventorySync(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION, 1);

        return array_values($collection->getItems());
    }
}
