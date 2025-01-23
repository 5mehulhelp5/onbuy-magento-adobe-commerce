<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\StopQueue;

use M2E\OnBuy\Model\ResourceModel\StopQueue as ResourceModel;

class Repository
{
    private ResourceModel\CollectionFactory $collectionFactory;
    /** @var \M2E\OnBuy\Model\ResourceModel\StopQueue */
    private ResourceModel $stopQueueResource;
    private \M2E\OnBuy\Model\ResourceModel\Account $accountResource;
    private \M2E\OnBuy\Model\ResourceModel\Site $siteResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\StopQueue $stopQueueResource,
        \M2E\OnBuy\Model\ResourceModel\Account $accountResource,
        \M2E\OnBuy\Model\ResourceModel\Site $siteResource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->stopQueueResource = $stopQueueResource;
        $this->accountResource = $accountResource;
        $this->siteResource = $siteResource;
    }

    public function create(\M2E\OnBuy\Model\StopQueue $stopQueue): void
    {
        $this->stopQueueResource->save($stopQueue);
    }

    public function save(\M2E\OnBuy\Model\StopQueue $stopQueue): void
    {
        $this->stopQueueResource->save($stopQueue);
    }

    /**
     * @param int $limit
     *
     * @return \M2E\OnBuy\Model\StopQueue[]
     */
    public function findNotProcessed(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ResourceModel::COLUMN_IS_PROCESSED, 0);
        $collection->setOrder(ResourceModel::COLUMN_CREATE_DATE, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $collection->getSelect()->limit($limit);

        return array_values($collection->getItems());
    }

    public function deleteCompletedAfterBorderDate(\DateTime $borderDate): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            [
                ResourceModel::COLUMN_IS_PROCESSED . ' = ?' => 1,
                ResourceModel::COLUMN_UPDATE_DATE . ' < ?' => $borderDate->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function getGroupedAccountAndSite(): array
    {
        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();
        $select = $connection->select();
        $select->from($collection->getMainTable())
               ->reset(\Magento\Framework\DB\Select::COLUMNS)
               ->columns(
                   [
                       'account_id',
                       'site_id',
                   ]
               )
               ->group('account_id')
               ->group('site_id')
               ->joinInner(
                   [
                       'account' => $this->accountResource->getMainTable()
                   ],
                   $collection->getMainTable() . ".account_id
                   = account.id",
                   ['server_hash']
               )
                ->joinInner(
                    [
                        'site' => $this->siteResource->getMainTable()
                    ],
                    $collection->getMainTable() . ".site_id
                       = site.id",
                    ['channel_site_id' => 'site_id']
                )
                ->where(ResourceModel::COLUMN_IS_PROCESSED . '=?', 0);

        return $connection->fetchAll($select);
    }

    public function getSkusByAccountSite(int $accountId, int $siteId, int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();
        $select = $connection->select();
        $select->from($collection->getMainTable())
               ->reset(\Magento\Framework\DB\Select::COLUMNS)
               ->columns(
                   [
                       'sku',
                   ]
               )
               ->limit($limit)
               ->where(ResourceModel::COLUMN_IS_PROCESSED . '=?', 0)
               ->where(ResourceModel::COLUMN_ACCOUNT_ID . '=?', $accountId)
               ->where(ResourceModel::COLUMN_SITE_ID . '=?', $siteId);

        return $connection->fetchCol($select);
    }

    public function massStatusUpdate(array $skus, int $accountId, int $siteId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->update(
            $collection->getMainTable(),
            [
                ResourceModel::COLUMN_IS_PROCESSED => 1,
            ],
            [
                ResourceModel::COLUMN_SKU . ' IN (?)' => $skus,
                ResourceModel::COLUMN_ACCOUNT_ID . ' = ?' => $accountId,
                ResourceModel::COLUMN_SITE_ID . ' = ?' => $siteId,
            ]
        );
    }
}
