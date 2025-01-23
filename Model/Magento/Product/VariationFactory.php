<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product;

class VariationFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\OnBuy\Model\Magento\Product $magentoProduct): Variation
    {
        return $this->objectManager->create(Variation::class, ['magentoProduct' => $magentoProduct]);
    }
}
