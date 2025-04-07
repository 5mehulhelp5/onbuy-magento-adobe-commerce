<?php

namespace M2E\OnBuy\Model\Category;

class CategoryAttributeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): CategoryAttribute
    {
        return $this->objectManager->create(CategoryAttribute::class);
    }
}
