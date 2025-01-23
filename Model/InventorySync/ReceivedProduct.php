<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync;

class ReceivedProduct extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::class);
    }

    public function create(
        int $accountId,
        int $siteId,
        string $sku
    ): self {
        $this->setData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_ACCOUNT_ID, $accountId)
             ->setData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_SITE_ID, $siteId)
             ->setData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_SKU, $sku);

        return $this;
    }

    public function getAccountId(): int
    {
        return $this->getData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_ACCOUNT_ID);
    }

    public function getSiteId(): int
    {
        return $this->getData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_SITE_ID);
    }

    public function getSku(): string
    {
        return $this->getData(\M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::COLUMN_SKU);
    }
}
