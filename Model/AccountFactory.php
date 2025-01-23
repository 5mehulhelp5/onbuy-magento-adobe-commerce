<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class AccountFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Account
    {
        return $this->objectManager->create(Account::class);
    }

    public function create(
        string $title,
        string $identifier,
        string $serverHash,
        bool $isTest,
        \M2E\OnBuy\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\OnBuy\Model\Account\Settings\Order $orderSettings,
        \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): Account {
        $model = $this->createEmpty();
        $model->create(
            $title,
            $identifier,
            $serverHash,
            $isTest,
            $unmanagedListingsSettings,
            $orderSettings,
            $invoicesAndShipmentSettings
        );

        return $model;
    }
}
