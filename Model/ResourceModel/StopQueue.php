<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel;

class StopQueue extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SITE_ID = 'site_id';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_IS_PROCESSED = 'is_processed';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_STOP_QUEUE,
            self::COLUMN_ID
        );
    }
}
