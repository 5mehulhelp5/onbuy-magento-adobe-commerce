<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

use M2E\OnBuy\Model\ResourceModel\Listing as ListingResource;
use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;
use M2E\OnBuy\Model\ResourceModel\Site as SiteResource;
use M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct as InventorySyncReceivedProductResource;

class Repository
{
    private ProductResource $productResource;
    private ProductResource\CollectionFactory $productCollectionFactory;
    private \M2E\OnBuy\Model\ProductFactory $productFactory;
    private \M2E\OnBuy\Model\ResourceModel\Listing $listingResource;
    private \M2E\OnBuy\Model\Product\AffectedProduct\Finder $affectedProductFinder;
    private \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper;
    /** @var \M2E\OnBuy\Model\ResourceModel\Site */
    private SiteResource $siteResource;
    /** @var \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct */
    private InventorySyncReceivedProductResource $inventorySyncReceivedProductResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Listing $listingResource,
        ProductResource $productResource,
        \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct $inventorySyncReceivedProductResource,
        ProductResource\CollectionFactory $productCollectionFactory,
        \M2E\OnBuy\Model\ProductFactory $productFactory,
        SiteResource $siteResource,
        \M2E\OnBuy\Model\Product\AffectedProduct\Finder $affectedProductFinder,
        \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->productResource = $productResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->listingResource = $listingResource;
        $this->affectedProductFinder = $affectedProductFinder;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->siteResource = $siteResource;
        $this->inventorySyncReceivedProductResource = $inventorySyncReceivedProductResource;
    }

    public function create(\M2E\OnBuy\Model\Product $product): void
    {
        $this->productResource->save($product);
    }

    public function save(
        \M2E\OnBuy\Model\Product $product
    ): \M2E\OnBuy\Model\Product {
        $this->productResource->save($product);

        return $product;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Product
    {
        $product = $this->productFactory->createEmpty();
        $this->productResource->load($product, $id);

        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    public function get(int $id): \M2E\OnBuy\Model\Product
    {
        $product = $this->find($id);
        if ($product === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic(sprintf('Listing Product with id "%s" not found.', $id));
        }

        return $product;
    }

    public function getProductsByMagentoProductId(
        int $magentoProductId,
        array $listingFilters = [],
        array $productFilters = []
    ): \M2E\OnBuy\Model\Product\AffectedProduct\Collection {
        return $this->affectedProductFinder->find(
            $magentoProductId,
            $listingFilters,
            $productFilters,
        );
    }

    public function delete(\M2E\OnBuy\Model\Product $product): void
    {
        $this->productResource->delete($product);
    }

    // ----------------------------------------

    /**
     * @return \M2E\OnBuy\Model\Product[]
     */
    public function findByListing(\M2E\OnBuy\Model\Listing $listing): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );

        return array_values($collection->getItems());
    }

    public function findByListingAndMagentoProductId(
        \M2E\OnBuy\Model\Listing $listing,
        int $magentoProductId
    ): ?\M2E\OnBuy\Model\Product {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );
        $collection->addFieldToFilter(
            ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        $product = $collection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    public function findProductsByMagentoSku(
        string $sku
    ): array {
        $collection = $this->productCollectionFactory->create();
        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()
                   ->join(
                       ['cpe' => $entityTableName],
                       sprintf(
                           'cpe.entity_id = `main_table`.%s',
                           ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                       ),
                       [],
                   );
        $collection->addFieldToFilter(
            'cpe.sku',
            ['like' => '%' . $sku . '%'],
        );

        return $collection->getItems();
    }

    public function findBySkuAndSiteId(
        string $sku,
        int $siteId
    ): ?\M2E\OnBuy\Model\Product {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(ProductResource::COLUMN_ONLINE_SKU, $sku);

        $collection
            ->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.%s = `main_table`.%s',
                    ListingResource::COLUMN_ID,
                    ProductResource::COLUMN_LISTING_ID,
                ),
                [],
            );

        $collection
            ->join(
                ['s' => $this->siteResource->getMainTable()],
                sprintf(
                    '`s`.%s = `l`.%s',
                    SiteResource::COLUMN_ID,
                    ListingResource::COLUMN_SITE_ID,
                ),
                []
            )
            ->addFieldToFilter(sprintf('s.%s', SiteResource::COLUMN_ID), $siteId);

        $product = $collection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    /**
     * @return \M2E\OnBuy\Model\Product[]
     */
    public function findByIds(array $productsIds): array
    {
        if (empty($productsIds)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_ID,
            ['in' => $productsIds],
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\OnBuy\Model\Product[]
     */
    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(
            ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        return array_values($collection->getItems());
    }

    public function findBySkusAccountSite(array $skus, int $accountId, int $siteId): array
    {
        return $this->findBySkus($skus, $accountId, $siteId);
    }

    public function findBySkusAccountListing(array $skus, int $accountId, int $listingId): array
    {
        return $this->findBySkus($skus, $accountId, null, $listingId);
    }

    /**
     * @param string[] $channelProductsSkus
     * @param int $accountId
     * @param int|null $siteId
     * @param int|null $listingId
     *
     * @return \M2E\OnBuy\Model\Product[]
     */
    private function findBySkus(
        array $channelProductsSkus,
        int $accountId,
        ?int $siteId = null,
        ?int $listingId = null
    ): array {
        if (empty($channelProductsSkus)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();

        $collection->addFieldToFilter(
            sprintf('main_table.%s', ProductResource::COLUMN_ONLINE_SKU),
            ['in' => $channelProductsSkus],
        );

        $collection
            ->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.%s = `main_table`.%s',
                    ListingResource::COLUMN_ID,
                    ProductResource::COLUMN_LISTING_ID,
                ),
                [],
            )
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ACCOUNT_ID), $accountId);

        if ($listingId !== null) {
            $collection->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ID), $listingId);
        }

        if ($siteId !== null) {
            $collection
                ->join(
                    ['s' => $this->siteResource->getMainTable()],
                    sprintf(
                        '`s`.%s = `l`.%s',
                        SiteResource::COLUMN_ID,
                        ListingResource::COLUMN_SITE_ID,
                    ),
                    []
                )
                ->addFieldToFilter(sprintf('s.%s', SiteResource::COLUMN_ID), $siteId);
        }

        return array_values($collection->getItems());
    }

    public function getCountListedProductsForListing(\M2E\OnBuy\Model\Listing $listing): int
    {
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addFieldToFilter(ProductResource::COLUMN_LISTING_ID, $listing->getId())
            ->addFieldToFilter(ProductResource::COLUMN_STATUS, \M2E\OnBuy\Model\Product::STATUS_LISTED);

        return (int)$collection->getSize();
    }

    /**
     * @param int $listingId
     *
     * @return int[]
     */
    public function findMagentoProductIdsByListingId(int $listingId): array
    {
        $collection = $this->productCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $collection
            ->addFieldToSelect(ProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addFieldToSelect(ProductResource::COLUMN_ID) // for load collection
            ->addFieldToFilter(ProductResource::COLUMN_LISTING_ID, $listingId);

        $result = [];
        foreach ($collection->getItems() as $product) {
            $result[] = $product->getMagentoProductId();
        }

        return $result;
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\OnBuy\Model\Product[]
     */
    public function massActionSelectedProducts(\Magento\Ui\Component\MassAction\Filter $filter): array
    {
        $collection = $this->productCollectionFactory->create();
        $filter->getCollection($collection);

        return array_values($collection->getItems());
    }

    /**
     * @return int[]
     */
    public function findRemovedMagentoProductIds(int $limit): array
    {
        $collection = $this->productCollectionFactory->create();

        $collection->getSelect()
                   ->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()
                   ->columns(
                       ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                   );
        $collection->getSelect()
                   ->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()
                   ->joinLeft(
                       ['cpe' => $entityTableName],
                       sprintf(
                           'cpe.entity_id = `main_table`.%s',
                           ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                       ),
                       [],
                   );

        $collection->getSelect()
                   ->where('cpe.entity_id IS NULL');
        $collection->getSelect()
                   ->limit($limit);

        $result = [];
        foreach ($collection->toArray()['items'] ?? [] as $row) {
            $result[] = (int)$row[ProductResource::COLUMN_MAGENTO_PRODUCT_ID];
        }

        return $result;
    }

    /**
     * @param int $accountId
     * @param int $siteId
     * @param \DateTime $inventorySyncProcessingStartDate
     *
     * @return \M2E\OnBuy\Model\Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findRemovedFromChannel(
        int $accountId,
        int $siteId,
        \DateTime $inventorySyncProcessingStartDate
    ): array {
        $collection = $this->productCollectionFactory->create();

        $collection->join(
            ['l' => $this->listingResource->getMainTable()],
            sprintf(
                '`l`.%s = `main_table`.%s',
                ListingResource::COLUMN_ID,
                ProductResource::COLUMN_LISTING_ID,
            ),
            [],
        );

        $collection->joinLeft(
            [
                'isrp' => $this->inventorySyncReceivedProductResource->getMainTable(),
            ],
            implode(' AND ', [
                sprintf(
                    '`isrp`.%s = `main_table`.%s',
                    InventorySyncReceivedProductResource::COLUMN_SKU,
                    ProductResource::COLUMN_ONLINE_SKU,
                ),
                sprintf(
                    '`isrp`.%s = `l`.%s',
                    InventorySyncReceivedProductResource::COLUMN_ACCOUNT_ID,
                    ListingResource::COLUMN_ACCOUNT_ID,
                ),
                sprintf(
                    '`isrp`.%s = `l`.%s',
                    InventorySyncReceivedProductResource::COLUMN_SITE_ID,
                    ListingResource::COLUMN_SITE_ID,
                ),
            ]),
            [],
        );

        $collection
            ->addFieldToFilter(
                sprintf('main_table.%s', ProductResource::COLUMN_STATUS),
                ['neq' => \M2E\OnBuy\Model\Product::STATUS_NOT_LISTED],
            )
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_SITE_ID), $siteId)
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter('isrp.id', ['null' => true]);
        /**
         * Excluding listing products created after current inventory sync processing start date
         */
        $collection->getSelect()->where(
            sprintf('main_table.%s ', ProductResource::COLUMN_ID)
            . 'NOT IN (?)',
            $this->getExcludedByDateSubSelect($inventorySyncProcessingStartDate)
        );

        return array_values($collection->getItems());
    }

    private function getExcludedByDateSubSelect(\DateTime $inventorySyncProcessingStartDate): \Zend_Db_Expr
    {
        return new \Zend_Db_Expr(
            sprintf(
                'SELECT `%s` FROM `%s` WHERE `%s`=%s AND `%s` > "%s"',
                ProductResource::COLUMN_ID,
                $this->productResource->getMainTable(),
                ProductResource::COLUMN_STATUS,
                \M2E\OnBuy\Model\Product::STATUS_LISTED,
                ProductResource::COLUMN_STATUS_CHANGE_DATE,
                $inventorySyncProcessingStartDate->format('Y-m-d H:i:s'),
            )
        );
    }

    // ----------------------------------------

    public function findIdsByListingId(int $listingId): array
    {
        if (empty($listingId)) {
            return [];
        }

        $select = $this->productResource->getConnection()
                                        ->select()
                                        ->from($this->productResource->getMainTable(), 'id')
                                        ->where('listing_id = ?', $listingId);

        return array_column($select->query()->fetchAll(), 'id');
    }

    public function updateLastBlockingErrorDate(array $productIds, \DateTime $dateTime): void
    {
        if (empty($productIds)) {
            return;
        }

        $this->productResource->getConnection()->update(
            $this->productResource->getMainTable(),
            [ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE => $dateTime->format('Y-m-d H:i:s')],
            ['id IN (?)' => $productIds]
        );
    }

    public function addProductTotalCountForListingCollection(
        \M2E\OnBuy\Model\ResourceModel\Listing\Collection $listingCollection
    ): void {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToSelect(ProductResource::COLUMN_LISTING_ID);
        $collection->addExpressionFieldToSelect(
            'products_total_count',
            'COUNT({{id}})',
            ['id' => ProductResource::COLUMN_ID]
        );
        $collection->getSelect()->group(ProductResource::COLUMN_LISTING_ID);

        $listingCollection->getSelect()
                          ->joinLeft(
                              ['t' => $collection->getSelect()],
                              'main_table.id=t.listing_id',
                              [
                                  'products_total_count' => 'products_total_count',
                              ]
                          );
    }

    public function getIds(int $fromId, int $limit): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('id', ['gt' => $fromId]);
        $collection->getSelect()->order(['id ASC']);
        $collection->getSelect()->limit($limit);

        return array_map('intval', $collection->getColumnValues('id'));
    }
}
