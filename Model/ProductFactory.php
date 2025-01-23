<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class ProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Product
    {
        return $this->objectManager->create(Product::class);
    }

    public function create(\M2E\OnBuy\Model\Listing $listing, int $magentoProductId): Product
    {
        $obj = $this->createEmpty();
        $obj->create($listing, $magentoProductId);

        return $obj;
    }
}
