<?php

declare(strict_types=1);

namespace M2E\OnBuy\Helper\Magento;

use M2E\OnBuy\Model\Magento\Product as ProductModel;

class Product
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_DOWNLOADABLE = 'downloadable';
    public const TYPE_CONFIGURABLE = 'configurable';
    public const TYPE_BUNDLE = 'bundle';
    public const TYPE_GROUPED = 'grouped';
    public const TYPE_VIRTUAL = 'virtual';

    public const SKU_MAX_LENGTH = 64;

    private \Magento\CatalogInventory\Model\Configuration $catalogInventoryConfiguration;
    private \M2E\OnBuy\Model\Config\Manager $config;

    private array $customLoadedProductTypes = [];

    public function __construct(
        \M2E\OnBuy\Model\Config\Manager $config,
        \Magento\CatalogInventory\Model\Configuration $catalogInventoryConfiguration
    ) {
        $this->catalogInventoryConfiguration = $catalogInventoryConfiguration;
        $this->config = $config;
    }

    // ----------------------------------------

    public function isSimpleType(string $originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_SIMPLE));
    }

    public function isDownloadableType(string $originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_DOWNLOADABLE));
    }

    public function isConfigurableType(string $originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_CONFIGURABLE));
    }

    public function isBundleType(string $originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_BUNDLE));
    }

    public function isGroupedType(string $originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_GROUPED));
    }

    // ---------------------------------------

    public function getOriginKnownTypes(?string $byLogicType = null): array
    {
        if (
            $byLogicType !== null
            && !in_array($byLogicType, $this->getLogicTypes())
        ) {
            throw new \LogicException('Unknown logic type.');
        }

        if ($byLogicType === null) {
            $originTypes = $this->getOriginTypes();
            foreach ($this->getLogicTypes() as $logicType) {
                $originTypes = array_merge($originTypes, $this->getOriginCustomTypes($logicType));
            }

            return array_unique($originTypes);
        }

        $associatedTypes = [
            self::TYPE_SIMPLE => [
                ProductModel::TYPE_SIMPLE_ORIGIN,
                ProductModel::TYPE_VIRTUAL_ORIGIN,
            ],
            self::TYPE_DOWNLOADABLE => [ProductModel::TYPE_DOWNLOADABLE_ORIGIN],
            self::TYPE_CONFIGURABLE => [ProductModel::TYPE_CONFIGURABLE_ORIGIN],
            self::TYPE_BUNDLE => [ProductModel::TYPE_BUNDLE_ORIGIN],
            self::TYPE_GROUPED => [ProductModel::TYPE_GROUPED_ORIGIN],
        ];

        $originTypes = array_unique(
            array_merge(
                $associatedTypes[$byLogicType],
                $this->getOriginCustomTypes($byLogicType)
            )
        );

        return $originTypes;
    }

    private function getOriginCustomTypes(string $byLogicType): array
    {
        if (isset($this->customLoadedProductTypes[$byLogicType])) {
            return $this->customLoadedProductTypes[$byLogicType];
        }

        if (!in_array($byLogicType, $this->getLogicTypes())) {
            throw new \LogicException('Unknown logic type.');
        }

        $customTypes = $this->config->get(
            "/magento/product/{$byLogicType}_type/",
            'custom_types'
        );

        $result = [];
        if (!empty($customTypes)) {
            $customTypes = explode(',', $customTypes);

            $result = !empty($customTypes) ? array_map('trim', $customTypes) : [];
        }

        return $this->customLoadedProductTypes[$byLogicType] = $result;
    }

    private function getLogicTypes(): array
    {
        return [
            self::TYPE_SIMPLE,
            self::TYPE_DOWNLOADABLE,
            self::TYPE_CONFIGURABLE,
            self::TYPE_BUNDLE,
            self::TYPE_GROUPED,
        ];
    }

    private function getOriginTypes(): array
    {
        return [
            ProductModel::TYPE_SIMPLE_ORIGIN,
            ProductModel::TYPE_VIRTUAL_ORIGIN,
            ProductModel::TYPE_CONFIGURABLE_ORIGIN,
            ProductModel::TYPE_BUNDLE_ORIGIN,
            ProductModel::TYPE_GROUPED_ORIGIN,
            ProductModel::TYPE_DOWNLOADABLE_ORIGIN,
        ];
    }

    // ----------------------------------------

    public function calculateStockAvailability($isInStock, $manageStock, $useConfigManageStock): bool
    {
        $manageStockGlobal = $this->catalogInventoryConfiguration->getManageStock();
        if (
            ($useConfigManageStock && !$manageStockGlobal)
            || (!$useConfigManageStock && !$manageStock)
        ) {
            return true;
        }

        return (bool)$isInStock;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function prepareAssociatedProducts(
        array $associatedProducts,
        \M2E\OnBuy\Model\Magento\Product $product
    ): array {
        $productType = $product->getTypeId();
        $productId = $product->getProductId();

        if (
            $this->isSimpleType($productType) ||
            $this->isDownloadableType($productType)
        ) {
            return [$productId];
        }

        if ($this->isBundleType($productType)) {
            $bundleAssociatedProducts = [];

            foreach ($associatedProducts as $key => $productIds) {
                $bundleAssociatedProducts[$key] = reset($productIds);
            }

            return $bundleAssociatedProducts;
        }

        if ($this->isConfigurableType($productType)) {
            $configurableAssociatedProducts = [];

            foreach ($associatedProducts as $productIds) {
                if (count($configurableAssociatedProducts) == 0) {
                    $configurableAssociatedProducts = $productIds;
                } else {
                    $configurableAssociatedProducts = array_intersect($configurableAssociatedProducts, $productIds);
                }
            }

            if (count($configurableAssociatedProducts) != 1) {
                throw new \M2E\OnBuy\Model\Exception\Logic(
                    'There is no associated Product found for Configurable Product.'
                );
            }

            return $configurableAssociatedProducts;
        }

        if ($this->isGroupedType($productType)) {
            return array_values($associatedProducts);
        }

        return [];
    }
}
