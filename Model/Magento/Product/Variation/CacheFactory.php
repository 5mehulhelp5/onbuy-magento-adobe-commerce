<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Variation;

class CacheFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\OnBuy\Model\Magento\Product\Cache $magentoProduct): Cache
    {
        return $this->objectManager->create(Cache::class, ['magentoProduct' => $magentoProduct]);
    }
}
