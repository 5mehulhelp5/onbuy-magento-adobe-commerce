<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing;

use M2E\OnBuy\Model\ResourceModel\Listing as ListingResource;
use M2E\OnBuy\Model\ResourceModel\Product as ListingProductResource;

class Repository
{
    use \M2E\Core\Model\Repository\CacheTrait;

    private \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Listing $listingResource;
    private \M2E\OnBuy\Model\ListingFactory $listingFactory;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\OnBuy\Model\ResourceModel\Product\Lock $productLockResource;
    private ListingProductResource $productResource;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Listing $listingResource,
        \M2E\OnBuy\Model\ListingFactory $listingFactory,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\OnBuy\Model\ResourceModel\Product\Lock $productLockResource,
        ListingProductResource $productResource
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingFactory = $listingFactory;
        $this->cache = $cache;
        $this->productLockResource = $productLockResource;
        $this->productResource = $productResource;
    }

    public function getListingsCount(): int
    {
        $collection = $this->listingCollectionFactory->create();

        return $collection->getSize();
    }

    public function get(int $id): \M2E\OnBuy\Model\Listing
    {
        $listing = $this->find($id);
        if ($listing === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Listing does not exist.');
        }

        return $listing;
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Listing
    {
        $listing = $this->listingFactory->createEmpty();

        $cacheData = $this->cache->getValue($this->makeCacheKey($listing, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($listing, $cacheData);

            return $listing;
        }

        $this->listingResource->load($listing, $id);

        if ($listing->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($listing, $id),
            $this->getCacheDate($listing),
            [],
            60 * 60
        );

        return $listing;
    }

    public function save(\M2E\OnBuy\Model\Listing $listing): void
    {
        $this->listingResource->save($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }

    public function remove(\M2E\OnBuy\Model\Listing $listing): void
    {
        $this->listingResource->delete($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     *
     * @return \M2E\OnBuy\Model\Listing[]
     */
    public function findForAccount(\M2E\OnBuy\Model\Account $account): array
    {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter(ListingResource::COLUMN_ACCOUNT_ID, $account->getId());

        return array_values($listingCollection->getItems());
    }

    /**
     * @return \M2E\OnBuy\Model\Listing[]
     */
    public function getAll(): array
    {
        $collection = $this->listingCollectionFactory->create();

        return array_values($collection->getItems());
    }

    public function hasProductsInSomeAction(\M2E\OnBuy\Model\Listing $listing): bool
    {
        $connection = $this->productResource->getConnection();

        $productTable = $this->productResource->getMainTable();
        $lockTable = $this->productLockResource->getMainTable();

        $select = $connection->select()
                             ->from(['p' => $productTable])
                             ->join(
                                 ['pl' => $lockTable],
                                 sprintf(
                                     'p.%s = pl.%s',
                                     \M2E\OnBuy\Model\ResourceModel\Product::COLUMN_ID,
                                     \M2E\OnBuy\Model\ResourceModel\Product\Lock::COLUMN_PRODUCT_ID,
                                 ),
                                 []
                             )
                             ->where(
                                 sprintf('p.%s = ?', \M2E\OnBuy\Model\ResourceModel\Product::COLUMN_LISTING_ID),
                                 $listing->getId()
                             )
                             ->limit(1);

        return (bool) $connection->fetchOne($select);
    }

    public function isExistListingByDescriptionPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $policyId);
    }

    public function isExistListingBySellingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $policyId);
    }

    public function isExistListingBySyncPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $policyId);
    }

    public function isExistListingByShippingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $policyId);
    }

    private function isExistListingByPolicy(
        string $columnName,
        int $policyId
    ): bool {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter($columnName, ['eq' => $policyId]);

        return $listingCollection->getSize() !== 0;
    }
}
