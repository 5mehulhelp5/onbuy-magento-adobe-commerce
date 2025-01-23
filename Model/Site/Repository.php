<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Site;

use M2E\OnBuy\Model\ResourceModel\Site as SiteResource;

class Repository
{
    use \M2E\Core\Model\Repository\CacheTrait;

    private \M2E\OnBuy\Model\SiteFactory $entityFactory;
    private \M2E\OnBuy\Model\ResourceModel\Site\CollectionFactory $collectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Site $resource;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\OnBuy\Model\SiteFactory $entityFactory,
        \M2E\OnBuy\Model\ResourceModel\Site\CollectionFactory $collectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Site $resource,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->cache = $cache;
    }

    public function create(\M2E\OnBuy\Model\Site $site): void
    {
        $this->resource->save($site);
    }

    public function save(\M2E\OnBuy\Model\Site $site): void
    {
        $this->resource->save($site);
        $this->cache->removeValue($this->makeCacheKey($site, $site->getId()));
    }

    public function remove(\M2E\OnBuy\Model\Site $site): void
    {
        $this->resource->delete($site);
        $this->cache->removeValue($this->makeCacheKey($site, $site->getId()));
    }

    public function find(int $id): ?\M2E\OnBuy\Model\Site
    {
        $site = $this->entityFactory->createEmpty();

        $cachedData = $this->cache->getValue($this->makeCacheKey($site, $id));
        if (!empty($cachedData)) {
            $this->initializeFromCache($site, $cachedData);

            return $site;
        }

        $this->resource->load($site, $id);

        if ($site->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($site, $id),
            $this->getCacheDate($site),
            [],
            60 * 60
        );

        return $site;
    }

    public function get(int $id): \M2E\OnBuy\Model\Site
    {
        $site = $this->find($id);
        if ($site === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Site not found.');
        }

        return $site;
    }

    /**
     * @return \M2E\OnBuy\Model\Site[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\OnBuy\Model\Site[]
     */
    public function getAllGroupBySiteId(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->group(\M2E\OnBuy\Model\ResourceModel\Site::COLUMN_SITE_ID);

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\OnBuy\Model\Site[]
     */
    public function findForAccount(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SiteResource::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);

        return array_values($collection->getItems());
    }
}
