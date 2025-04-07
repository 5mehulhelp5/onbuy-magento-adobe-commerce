<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy;

use M2E\OnBuy\Model\Policy\Description;

class DescriptionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Description
    {
        return $this->objectManager->create(Description::class);
    }
}
