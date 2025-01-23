<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

class QtyCalculatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\OnBuy\Model\Product $product): QtyCalculator
    {
        return $this->objectManager->create(QtyCalculator::class, ['product' => $product]);
    }
}
