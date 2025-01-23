<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel;

class Site extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SITE_ID = 'site_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_COUNTRY_CODE = 'country_code';
    public const COLUMN_CURRENCY_CODE = 'currency_code';
    public const COLUMN_INVENTORY_LAST_SYNC_DATE = 'inventory_last_synchronization_date';
    public const COLUMN_ORDER_LAST_SYNC = 'orders_last_synchronization';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_SITE,
            self::COLUMN_ID
        );
    }
}
