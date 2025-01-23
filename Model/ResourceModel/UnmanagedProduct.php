<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel;

class UnmanagedProduct extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SITE_ID = 'site_id';
    public const COLUMN_CHANNEL_PRODUCT_ID = 'channel_product_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_PRODUCT_URL = 'product_url';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_GROUP_SKU = 'group_sku';
    public const COLUMN_OPC = 'opc';
    public const COLUMN_PRODUCT_ENCODED_ID = 'product_encoded_id';
    public const COLUMN_IDENTIFIERS = 'identifiers';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_CURRENCY_CODE = 'currency_code';
    public const COLUMN_HANDLING_TIME = 'handling_time';
    public const COLUMN_QTY = 'qty';
    public const COLUMN_CONDITIONS = 'conditions';
    public const COLUMN_CONDITIONS_NOTES = 'conditions_notes';
    public const COLUMN_DELIVERY_WEIGHT = 'delivery_weight';
    public const COLUMN_DELIVERY_TEMPLATE_ID = 'delivery_template_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_UNMANAGED_PRODUCT,
            self::COLUMN_ID
        );
    }
}
