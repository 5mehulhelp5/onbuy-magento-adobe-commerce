<?php

namespace M2E\OnBuy\Model\Magento\Product\Variation;

class Cache extends \M2E\OnBuy\Model\Magento\Product\Variation
{
    public function __construct(
        \M2E\OnBuy\Model\Magento\Product\Cache $magentoProduct,
        \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $entityOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $productOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Bundle\Model\OptionFactory $bundleOptionFactory,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleSelectionCollectionFactory,
        \Magento\Downloadable\Model\LinkFactory $downloadableLinkFactory
    ) {
        parent::__construct(
            $magentoProduct,
            $ourMagentoProductFactory,
            $productFactory,
            $entityOptionCollectionFactory,
            $productOptionCollectionFactory,
            $storeManager,
            $bundleOptionFactory,
            $bundleSelectionCollectionFactory,
            $downloadableLinkFactory
        );
    }

    public function getVariationsTypeStandard()
    {
        $params = [
            'virtual_attributes' => $this->getMagentoProduct()->getVariationVirtualAttributes(),
            'filter_attributes' => $this->getMagentoProduct()->getVariationFilterAttributes(),
            'is_ignore_virtual_attributes' => $this->getMagentoProduct()->isIgnoreVariationVirtualAttributes(),
            'is_ignore_filter_attributes' => $this->getMagentoProduct()->isIgnoreVariationFilterAttributes(),
        ];

        return $this->getMethodData(__FUNCTION__, $params);
    }

    public function getVariationsTypeRaw()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTitlesVariationSet()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    protected function getMethodData($methodName, $params = null)
    {
        $cacheKey = [
            __CLASS__,
            $methodName,
        ];

        if ($params !== null) {
            $cacheKey[] = $params;
        }

        /** @var \M2E\OnBuy\Model\Magento\Product\Cache  $magentoProduct */
        $magentoProduct = $this->getMagentoProduct();

        $cacheResult = $magentoProduct->getCacheValue($cacheKey);

        if (
            $magentoProduct->isCacheEnabled()
            && $cacheResult !== null
        ) {
            return $cacheResult;
        }

        $data = parent::$methodName();

        if (!$magentoProduct->isCacheEnabled()) {
            return $data;
        }

        return $magentoProduct->setCacheValue($cacheKey, $data);
    }
}
