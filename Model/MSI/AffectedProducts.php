<?php

namespace M2E\OnBuy\Model\MSI;

use Magento\InventoryIndexer\Indexer\Source\GetAssignedStockIds;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

class AffectedProducts
{
    private array $runtimeCache = [];

    /** @var \Magento\Store\Api\WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var \Magento\Catalog\Model\ResourceModel\Product */
    private $productResource;

    // ---------------------------------------

    /** @var \Magento\InventoryIndexer\Indexer\Source\GetAssignedStockIds */
    private $getAssignedStockIds;

    /** @var \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface */
    private $getAssignedChannels;

    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;
    private \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->productResource = $productResource;

        $this->getAssignedStockIds = $objectManager->get(GetAssignedStockIds::class);
        $this->getAssignedChannels = $objectManager->get(GetAssignedSalesChannelsForStockInterface::class);
        $this->listingProductRepository = $listingProductRepository;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    /**
     * @param $sourceCode
     *
     * @return array
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedStoresBySource($sourceCode)
    {
        $cacheKey = __METHOD__ . $sourceCode;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = [];
        foreach ($this->getAssignedStockIds->execute([$sourceCode]) as $stockId) {
            foreach ($this->getAffectedStoresByStock($stockId) as $storeId) {
                $storesIds[$storeId] = $storeId;
            }
        }
        $storesIds = array_values($storesIds);

        $this->setToRuntimeCache($cacheKey, $storesIds);

        return $storesIds;
    }

    /**
     * @param $stockId
     *
     * @return array
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedStoresByStock($stockId)
    {
        $cacheKey = __METHOD__ . $stockId;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = [];
        foreach ($this->getAssignedChannels->execute($stockId) as $channel) {
            if ($channel->getType() !== SalesChannelInterface::TYPE_WEBSITE) {
                continue;
            }

            foreach ($this->getAffectedStoresByChannel($channel->getCode()) as $storeId) {
                $storesIds[$storeId] = $storeId;
            }
        }
        $storesIds = array_values($storesIds);

        $this->setToRuntimeCache($cacheKey, $storesIds);

        return $storesIds;
    }

    /**
     * @param $channelCode
     *
     * @return array
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedStoresByChannel($channelCode)
    {
        $cacheKey = __METHOD__ . $channelCode;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = [];
        try {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->websiteRepository->get($channelCode);

            foreach ($website->getStoreIds() as $storeId) {
                $storesIds[$storeId] = (int)$storeId;
            }

            if ($website->getIsDefault()) {
                $storesIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noSuchEntityException) {
            return [];
        }
        $storesIds = array_values($storesIds);

        $this->setToRuntimeCache($cacheKey, $storesIds);

        return $storesIds;
    }

    /**
     * @param $sourceCode
     *
     * @return \M2E\OnBuy\Model\Listing[]
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedListingsBySource($sourceCode)
    {
        $cacheKey = __METHOD__ . $sourceCode;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = $this->getAffectedStoresBySource($sourceCode);
        if (empty($storesIds)) {
            return [];
        }

        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter('store_id', ['in' => $storesIds]);

        $this->setToRuntimeCache($cacheKey, $collection->getItems());

        return $collection->getItems();
    }

    /**
     * @param $stockId
     *
     * @return \M2E\OnBuy\Model\Listing[]
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedListingsByStock($stockId)
    {
        $cacheKey = __METHOD__ . $stockId;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = $this->getAffectedStoresByStock($stockId);
        if (empty($storesIds)) {
            return [];
        }

        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter('store_id', ['in' => $storesIds]);

        $this->setToRuntimeCache($cacheKey, $collection->getItems());

        return $collection->getItems();
    }

    /**
     * @param $channelCode
     *
     * @return \M2E\OnBuy\Model\Listing[]
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedListingsByChannel($channelCode)
    {
        $cacheKey = __METHOD__ . $channelCode;
        $cacheValue = $this->getFromRuntimeCache($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $storesIds = $this->getAffectedStoresByChannel($channelCode);
        if (empty($storesIds)) {
            return [];
        }

        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter('store_id', ['in' => $storesIds]);

        $this->setToRuntimeCache($cacheKey, $collection->getItems());

        return $collection->getItems();
    }

    //########################################

    /**
     * @param $sourceCode
     * @param $sku
     *
     * @return \M2E\OnBuy\Model\Product\AffectedProduct\Collection
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedProductsBySourceAndSku(
        $sourceCode,
        $sku
    ): \M2E\OnBuy\Model\Product\AffectedProduct\Collection {
        $storesIds = $this->getAffectedStoresBySource($sourceCode);
        if (empty($storesIds)) {
            return new \M2E\OnBuy\Model\Product\AffectedProduct\Collection();
        }

        return $this->listingProductRepository->getProductsByMagentoProductId(
            (int)$this->productResource->getIdBySku($sku),
            ['store_id' => $storesIds]
        );
    }

    /**
     * @param $stockId
     * @param $sku
     *
     * @return \M2E\OnBuy\Model\Product\AffectedProduct\Collection
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAffectedProductsByStockAndSku(
        $stockId,
        $sku
    ): \M2E\OnBuy\Model\Product\AffectedProduct\Collection {
        $storesIds = $this->getAffectedStoresByStock($stockId);
        if (empty($storesIds)) {
            return new \M2E\OnBuy\Model\Product\AffectedProduct\Collection();
        }

        return $this->listingProductRepository->getProductsByMagentoProductId(
            (int)$this->productResource->getIdBySku($sku),
            ['store_id' => $storesIds]
        );
    }

    // ----------------------------------------

    private function setToRuntimeCache($key, $value): void
    {
        $this->runtimeCache[$key] = $value;
    }

    private function getFromRuntimeCache($key)
    {
        return $this->runtimeCache[$key] ?? null;
    }
}
