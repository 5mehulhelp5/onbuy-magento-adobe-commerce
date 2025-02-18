<?php

namespace M2E\OnBuy\Model\Policy;

class ShippingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Shipping
    {
        return $this->objectManager->create(Shipping::class);
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        int $siteId,
        string $title,
        int $deliveryTemplateId
    ): Shipping {
        $model = $this->createEmpty();
        $model->create(
            $account->getId(),
            $siteId,
            $title,
            $deliveryTemplateId
        );

        return $model;
    }
}
