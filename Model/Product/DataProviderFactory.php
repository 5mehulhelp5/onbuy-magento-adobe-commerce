<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

use M2E\OnBuy\Model\Product\DataProvider;

class DataProviderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Product $product
    ): DataProvider {
        return $this->objectManager->create(
            DataProvider::class,
            [
                'product' => $product,
            ]
        );
    }
}
