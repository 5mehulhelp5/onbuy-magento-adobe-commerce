<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\InspectDirectChanges;

class Config
{
    public const GROUP = '/listing/product/inspector/';
    public const KEY_MAX_ALLOWED_PRODUCT_COUNT = 'max_allowed_products_count';

    private \M2E\OnBuy\Model\Config\Manager $config;
    private \M2E\OnBuy\Model\Module\Configuration $moduleConfig;

    public function __construct(
        \M2E\OnBuy\Model\Config\Manager $config,
        \M2E\OnBuy\Model\Module\Configuration $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->config = $config;
    }

    public function isEnableProductInspectorMode(): bool
    {
        return (bool)$this->moduleConfig->getProductInspectorMode();
    }

    public function getMaxAllowedProducts(): int
    {
        return (int)$this->config->getGroupValue(
            self::GROUP,
            self::KEY_MAX_ALLOWED_PRODUCT_COUNT
        );
    }
}
