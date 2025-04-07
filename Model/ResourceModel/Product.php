<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel;

class Product extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_CHANNEL_PRODUCT_ID = 'channel_product_id';
    public const COLUMN_IS_PRODUCT_CREATOR = 'is_product_creator';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_ONLINE_SKU = 'online_sku';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_STATUS_CHANGER = 'status_changer';
    public const COLUMN_STATUS_CHANGE_DATE = 'status_change_date';
    public const COLUMN_ONLINE_TITLE = 'online_title';
    public const COLUMN_ONLINE_PRODUCT_URL = 'online_product_url';
    public const COLUMN_ONLINE_GROUP_SKU = 'online_group_sku';
    public const COLUMN_OPC = 'opc';
    public const COLUMN_PRODUCT_ENCODED_ID = 'product_encoded_id';
    public const COLUMN_IDENTIFIERS = 'identifiers';

    public const COLUMN_ONLINE_QTY = 'online_qty';
    public const COLUMN_ONLINE_PRICE = 'online_price';
    public const COLUMN_TEMPLATE_CATEGORY_ID  = 'template_category_id';

    public const COLUMN_LAST_BLOCKING_ERROR_DATE = 'last_blocking_error_date';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
            self::COLUMN_ID
        );
    }

    public function getTemplateCategoryIds(array $listingProductIds, $columnName, $returnNull = false): array
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['product' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns([$columnName])
                       ->where('id IN (?)', $listingProductIds);

        !$returnNull && $select->where("{$columnName} IS NOT NULL");

        foreach ($select->query()->fetchAll() as $row) {
            $id = $row[$columnName] !== null ? (int)$row[$columnName] : null;
            if (!$returnNull) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }
}
