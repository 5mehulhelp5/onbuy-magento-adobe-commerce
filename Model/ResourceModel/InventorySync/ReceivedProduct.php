<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\InventorySync;

class ReceivedProduct extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SITE_ID = 'site_id';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_INVENTORY_SYNC_RECEIVED_PRODUCT,
            self::COLUMN_ID
        );
    }
}
