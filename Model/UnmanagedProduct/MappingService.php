<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

use M2E\OnBuy\Model\Magento\Product as ProductModel;

/**
 * @psalm-suppress UndefinedClass
 */
class MappingService
{
    private \Magento\Catalog\Model\ProductFactory $productFactory;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory
    ) {
        $this->productFactory = $productFactory;
        $this->unmanagedRepository = $unmanagedRepository;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    /**
     * @param \M2E\OnBuy\Model\UnmanagedProduct[] $unmanagedProducts
     *
     * @return bool
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function autoMapUnmanagedProducts(array $unmanagedProducts): bool
    {
        $unmanagedProductsFiltered = array_filter($unmanagedProducts, function ($unmanaged) {
            return !$unmanaged->hasMagentoProductId();
        });

        if (empty($unmanagedProductsFiltered)) {
            return false;
        }

        $result = true;
        foreach ($unmanagedProductsFiltered as $unmanaged) {
            if (!$this->autoMapUnmanagedProduct($unmanaged)) {
                $result = false;
            }
        }

        return $result;
    }

    private function autoMapUnmanagedProduct(\M2E\OnBuy\Model\UnmanagedProduct $unmanaged): bool
    {
        if ($unmanaged->hasMagentoProductId()) {
            return false;
        }

        if (!$unmanaged->getAccount()->getUnmanagedListingSettings()->isMappingEnabled()) {
            return false;
        }

        $magentoProduct = $this->findMagentoProduct($unmanaged);
        if ($magentoProduct === null) {
            return false;
        }

        return $this->mapProduct($unmanaged, $magentoProduct);
    }

    // ----------------------------------------

    private function findMagentoProduct(
        \M2E\OnBuy\Model\UnmanagedProduct $unmanaged
    ): ?\Magento\Catalog\Model\Product {
        $mappingTypes = $unmanaged->getAccount()->getUnmanagedListingSettings()->getMappingTypesByPriority();

        foreach ($mappingTypes as $type) {
            $magentoProduct = $this->findMagentoProductIdByMappingType($type, $unmanaged);

            if ($magentoProduct !== null) {
                return $magentoProduct;
            }
        }

        return null;
    }

    private function findMagentoProductIdByMappingType(
        string $type,
        \M2E\OnBuy\Model\UnmanagedProduct $unmanaged
    ): ?\Magento\Catalog\Model\Product {
        switch ($type) {
            case \M2E\OnBuy\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_SKU:
                return $this->findSkuMappedMagentoProductId($unmanaged);
            case \M2E\OnBuy\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_TITLE:
                return $this->findTitleMappedMagentoProductId($unmanaged);
            case \M2E\OnBuy\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_OPC:
                return $this->findOpcMappedMagentoProductId($unmanaged);
            default:
                return null;
        }
    }

    private function findSkuMappedMagentoProductId(
        \M2E\OnBuy\Model\UnmanagedProduct $unmanaged
    ): ?\Magento\Catalog\Model\Product {
        $temp = $unmanaged->getSku();

        if (empty($temp)) {
            return null;
        }

        $settings = $unmanaged->getAccount()->getUnmanagedListingSettings();

        if ($settings->isMappingBySkuModeByProductId()) {
            $productId = trim($unmanaged->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return null;
            }

            $product = $this->productFactory->create()->load($productId);

            if (
                $product->getId()
                && $this->isMagentoProductTypeAllowed($product->getTypeId())
            ) {
                return $product;
            }

            return null;
        }

        $attributeCode = null;

        if ($settings->isMappingBySkuModeBySku()) {
            $attributeCode = 'sku';
        }

        if ($settings->isMappingBySkuModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeBySku();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $unmanaged->getRelatedStoreId();
        $attributeValue = trim($unmanaged->getSku());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj instanceof \Magento\Catalog\Model\Product
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return $productObj;
        }

        return null;
    }

    private function findTitleMappedMagentoProductId(
        \M2E\OnBuy\Model\UnmanagedProduct $unmanaged
    ): ?\Magento\Catalog\Model\Product {
        $temp = $unmanaged->getTitle();

        if (empty($temp)) {
            return null;
        }

        $settings = $unmanaged->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByTitleModeByProductName()) {
            $attributeCode = 'name';
        }

        if ($settings->isMappingByTitleModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeByTitle();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $unmanaged->getRelatedStoreId();
        $attributeValue = trim($unmanaged->getTitle());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj instanceof \Magento\Catalog\Model\Product
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return $productObj;
        }

        return null;
    }

    private function findOpcMappedMagentoProductId(
        \M2E\OnBuy\Model\UnmanagedProduct $unmanaged
    ): ?\Magento\Catalog\Model\Product {
        $temp = $unmanaged->getOpc();

        if (empty($temp)) {
            return null;
        }

        $settings = $unmanaged->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByOpcEnabled()) {
            $attributeCode = $settings->getMappingAttributeByOpc();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $unmanaged->getRelatedStoreId();
        $attributeValue = $unmanaged->getOpc();

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj instanceof \Magento\Catalog\Model\Product
            && $productObj->getId()
        ) {
            return $productObj;
        }

        return null;
    }

    private function isMagentoProductTypeAllowed($type): bool
    {
        $allowedTypes = [
            ProductModel::TYPE_SIMPLE_ORIGIN,
            ProductModel::TYPE_VIRTUAL_ORIGIN,
        ];

        return in_array($type, $allowedTypes);
    }

    // ----------------------------------------

    private function mapProduct(
        \M2E\OnBuy\Model\UnmanagedProduct $unmanagedProduct,
        \Magento\Catalog\Model\Product $magentoProduct
    ): bool {
        $unmanagedProduct->mapToMagentoProduct((int)$magentoProduct->getId());
        $this->unmanagedRepository->save($unmanagedProduct);

        return true;
    }

    // ----------------------------------------

    public function manualMapProduct(int $unmanagedId, int $productId): bool
    {
        $unmanagedProduct = $this->unmanagedRepository->findById($unmanagedId);
        if (!$unmanagedProduct) {
            return false;
        }

        $magentoProduct = $this->magentoProductFactory->createByProductId($productId);

        return $this->mapProduct($unmanagedProduct, $magentoProduct->getProduct());
    }

    public function unmapProduct(\M2E\OnBuy\Model\UnmanagedProduct $product): void
    {
        $product->unmapFromMagentoProduct();
        $this->unmanagedRepository->save($product);
    }
}
