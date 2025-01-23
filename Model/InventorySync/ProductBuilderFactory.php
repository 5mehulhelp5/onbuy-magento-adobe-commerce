<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync;

class ProductBuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): \M2E\OnBuy\Model\InventorySync\ProductBuilder {
        return $this->objectManager->create(
            \M2E\OnBuy\Model\InventorySync\ProductBuilder::class,
            [
                'account' => $account,
                'site' => $site
            ],
        );
    }
}
