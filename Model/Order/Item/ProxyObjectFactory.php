<?php

namespace M2E\OnBuy\Model\Order\Item;

class ProxyObjectFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\OnBuy\Model\Order\Item $orderItem
    ): ProxyObject {
        return $this->objectManager->create(ProxyObject::class, ['item' => $orderItem]);
    }
}
