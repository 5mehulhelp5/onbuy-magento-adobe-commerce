<?php

namespace M2E\OnBuy\Model\Magento\Product;

class Cache extends \M2E\OnBuy\Model\Magento\Product
{
    private bool $isCacheEnabled = false;
    private array $runtimeCache = [];
    private Variation\CacheFactory $variationCacheFactory;

    public function __construct(
        \M2E\OnBuy\Model\Magento\ProductFactory $m2eMagentoProductFactory,
        \M2E\OnBuy\Model\Magento\Product\Variation\CacheFactory $variationCacheFactory,
        \M2E\OnBuy\Model\Magento\Product\Inventory\Factory $inventoryFactory,
        \Magento\Framework\Filesystem\DriverPool $driverPool,
        \Magento\Framework\App\ResourceConnection $resourceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \M2E\OnBuy\Model\Magento\Product\Type\ConfigurableFactory $configurableFactory,
        \M2E\OnBuy\Model\Magento\Product\Status $productStatus,
        \Magento\CatalogInventory\Model\Configuration $catalogInventoryConfiguration,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ImageFactory $magentoImageFactory,
        \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \M2E\OnBuy\Model\Module\Configuration $moduleConfiguration,
        \M2E\OnBuy\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\OnBuy\Helper\Magento\Product $magentoProductHelper,
        \M2E\OnBuy\Model\Magento\Product\ImageFactory $imageFactory,
        \M2E\OnBuy\Model\Magento\Product\VariationFactory $variationFactory
    ) {
        parent::__construct(
            $m2eMagentoProductFactory,
            $inventoryFactory,
            $driverPool,
            $resourceModel,
            $productFactory,
            $websiteFactory,
            $productType,
            $configurableFactory,
            $productStatus,
            $catalogInventoryConfiguration,
            $storeFactory,
            $filesystem,
            $magentoImageFactory,
            $magentoProductCollectionFactory,
            $resourceProduct,
            $moduleConfiguration,
            $dbStructureHelper,
            $globalDataHelper,
            $cache,
            $magentoProductHelper,
            $imageFactory,
            $variationFactory,
        );

        $this->variationCacheFactory = $variationCacheFactory;
    }

    // ----------------------------------------

    public function isCacheEnabled(): bool
    {
        return $this->isCacheEnabled;
    }

    public function enableCache(): self
    {
        $this->isCacheEnabled = true;

        return $this;
    }

    public function disableCache(): self
    {
        $this->isCacheEnabled = false;

        return $this;
    }

    // ----------------------------------------

    public function exists()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeInstance()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockItem()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTypeId()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isSimpleTypeWithCustomOptions(): bool
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getSku()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getName()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isStatusEnabled(): bool
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function isStockAvailability(): bool
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getSpecialPrice()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getQty(bool $lifeMode = false): int
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getAttributeValue($attributeCode, $convertBoolean = true): string
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getThumbnailImage()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getImage($attribute = 'image')
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getGalleryImages($limitImages = 0)
    {
        $args = func_get_args();

        return $this->getMethodData(__FUNCTION__, $args);
    }

    public function getVariationInstance()
    {
        if ($this->_variationInstance !== null) {
            return $this->_variationInstance;
        }

        $this->_variationInstance = $this->variationCacheFactory->create($this);

        return $this->_variationInstance;
    }

    private function getMethodData($methodName, $params = null)
    {
        $cacheKey = [
            __CLASS__,
            $methodName,
        ];

        if ($params !== null) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getCacheValue($cacheKey);

        if (
            $this->isCacheEnabled()
            && $cacheResult !== null
        ) {
            return $cacheResult;
        }

        if ($params !== null) {
            $data = parent::$methodName(...$params);
        } else {
            $data = parent::$methodName();
        }

        if (!$this->isCacheEnabled()) {
            return $data;
        }

        return $this->setCacheValue($cacheKey, $data);
    }

    public function setCacheValue(array $externalKey, $value)
    {
        $key = sha1(
            'magento_product_'
            . $this->getProductId()
            . '_'
            . $this->getStoreId()
            . '_'
            . json_encode($externalKey),
        );

        $this->runtimeCache[$key] = $value;

        return $value;
    }

    public function getCacheValue(array $externalKey)
    {
        $key = sha1(
            'magento_product_'
            . $this->getProductId()
            . '_'
            . $this->getStoreId()
            . '_'
            . json_encode($externalKey),
        );

        return $this->runtimeCache[$key] ?? null;
    }
}
