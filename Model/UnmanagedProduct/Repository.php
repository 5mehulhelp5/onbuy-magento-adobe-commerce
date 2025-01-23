<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

use M2E\OnBuy\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;
use M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct as InventorySyncReceivedProductResource;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;

class Repository
{
    private \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct\CollectionFactory $collectionUnmanagedFactory;
    private \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct $unmanagedResource;
    private \M2E\OnBuy\Model\UnmanagedProductFactory $objectFactory;
    private \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper;
    /** @var \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct */
    private InventorySyncReceivedProductResource $inventorySyncReceivedProductResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct $unmanagedResource,
        \M2E\OnBuy\Model\UnmanagedProductFactory $unmanagedProductFactory,
        \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct $inventorySyncReceivedProductResource,
        \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->collectionUnmanagedFactory = $collectionFactory;
        $this->unmanagedResource = $unmanagedResource;
        $this->objectFactory = $unmanagedProductFactory;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->inventorySyncReceivedProductResource = $inventorySyncReceivedProductResource;
    }

    public function createCollection(): \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct\Collection
    {
        return $this->collectionUnmanagedFactory->create();
    }

    public function create(\M2E\OnBuy\Model\UnmanagedProduct $unmanaged): void
    {
        $this->unmanagedResource->save($unmanaged);
    }

    public function save(\M2E\OnBuy\Model\UnmanagedProduct $unmanaged): void
    {
        $this->unmanagedResource->save($unmanaged);
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function get(int $id): \M2E\OnBuy\Model\UnmanagedProduct
    {
        $obj = $this->objectFactory->createEmpty();
        $this->unmanagedResource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\OnBuy\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function delete(\M2E\OnBuy\Model\UnmanagedProduct $listingProduct): void
    {
        $this->unmanagedResource->delete($listingProduct);
    }

    /**
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param int $id
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct|null
     */
    public function findById(int $id): ?\M2E\OnBuy\Model\UnmanagedProduct
    {
        $obj = $this->objectFactory->createEmpty();
        $this->unmanagedResource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    /**
     * @param string[] $skus
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     */
    public function findBySkusAccountSite(array $skus, int $accountId, int $siteId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection
            ->addFieldToFilter(
                UnmanagedProductResource::COLUMN_SKU,
                ['in' => $skus],
            )
            ->addFieldToFilter(UnmanagedProductResource::COLUMN_ACCOUNT_ID, ['eq' => $accountId])
            ->addFieldToFilter(UnmanagedProductResource::COLUMN_SITE_ID, ['eq' => $siteId]);

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     *
     * @return void
     */
    public function removeProductByAccount(int $accountId): void
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    /**
     * @param int $magentoProductId
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     */
    public function findProductByMagentoProduct(int $magentoProductId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return array_values($collection->getItems());
    }

    public function findRemovedMagentoProductIds(): array
    {
        $collection = $this->createCollection();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID
        );
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, ['notnull' => true]);
        $collection->getSelect()->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            ['cpe' => $entityTableName],
            sprintf(
                'cpe.entity_id = `main_table`.%s',
                UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID
            ),
            []
        );
        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $result = [];
        foreach ($collection->toArray()['items'] ?? [] as $row) {
            $result[] = (int)$row[UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID];
        }

        return $result;
    }

    public function findRemovedFromChannel(int $accountId, int $siteId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();

        $collection->joinLeft(
            [
                'isrp' => $this->inventorySyncReceivedProductResource->getMainTable(),
            ],
            implode(' AND ', [
                sprintf(
                    '`isrp`.%s = `main_table`.%s',
                    InventorySyncReceivedProductResource::COLUMN_SKU,
                    UnmanagedProductResource::COLUMN_SKU,
                ),
                sprintf(
                    '`isrp`.%s = `main_table`.%s',
                    InventorySyncReceivedProductResource::COLUMN_ACCOUNT_ID,
                    UnmanagedProductResource::COLUMN_ACCOUNT_ID,
                ),
                sprintf(
                    '`isrp`.%s = `main_table`.%s',
                    InventorySyncReceivedProductResource::COLUMN_SITE_ID,
                    UnmanagedProductResource::COLUMN_SITE_ID,
                ),
            ]),
            [],
        );

        $collection
            ->addFieldToFilter(sprintf('main_table.%s', UnmanagedProductResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter(sprintf('main_table.%s', UnmanagedProductResource::COLUMN_SITE_ID), $siteId)
            ->addFieldToFilter('isrp.id', ['null' => true]);

        return array_values($collection->getItems());
    }

    // ----------------------------------------

    public function isExistForAccount(int $accountId): bool
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_ACCOUNT_ID, $accountId);

        return (int)$collection->getSize() > 0;
    }

    public function findBySkuAndSite(string $sku, int $siteId): ?\M2E\OnBuy\Model\UnmanagedProduct
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_SKU, $sku);
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_SITE_ID, $siteId);

        /**
         *
         * @var \M2E\OnBuy\Model\UnmanagedProduct $item
         */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    /**
     * @param array $ids
     * @param int $accountId
     *
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function findSiteIdByUnmanagedIdsAndAccount(array $ids, int $accountId)
    {
        $listingOtherCollection = $this->collectionUnmanagedFactory->create();
        $listingOtherCollection->addFieldToFilter('id', ['in' => $ids]);
        $listingOtherCollection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $listingOtherCollection->getSelect()->join(
            ['cpe' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity')],
            'magento_product_id = cpe.entity_id'
        );

        $listingOtherCollection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return $listingOtherCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->group(['site_id'])
            ->columns(['site_id'])
            ->query()
            ->fetch();
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForUnmappingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForMovingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForAutoMappingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['null' => true]
        );

        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }
}
