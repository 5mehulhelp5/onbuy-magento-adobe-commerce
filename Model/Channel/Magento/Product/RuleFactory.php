<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Channel\Magento\Product;

class RuleFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\OnBuy\Model\Channel\Magento\Product\Rule
    {
        return $this->objectManager->create(\M2E\OnBuy\Model\Channel\Magento\Product\Rule::class);
    }
}
