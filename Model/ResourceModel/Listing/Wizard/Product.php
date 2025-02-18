<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Listing\Wizard;

class Product extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_WIZARD_ID = 'wizard_id';
    public const COLUMN_UNMANAGED_PRODUCT_ID = 'unmanaged_product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_CHANNEL_PRODUCT_ID = 'channel_product_id';
    public const COLUMN_CHANNEL_PRODUCT_ID_SEARCH_STATUS = 'channel_product_id_search_status';
    public const COLUMN_CHANNEL_PRODUCT_DATA = 'channel_product_data';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_IS_PROCESSED = 'is_processed';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT,
            self::COLUMN_ID
        );
    }
}
