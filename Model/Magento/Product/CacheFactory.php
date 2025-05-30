<?php

namespace M2E\OnBuy\Model\Magento\Product;

class CacheFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Cache
    {
        return $this->objectManager->create(Cache::class);
    }
}
