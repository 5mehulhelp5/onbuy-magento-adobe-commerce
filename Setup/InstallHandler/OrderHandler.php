<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\InstallHandler;

use M2E\OnBuy\Helper\Module\Database\Tables as TablesHelper;
use M2E\OnBuy\Model\ResourceModel\Order as OrderResource;
use M2E\OnBuy\Model\ResourceModel\Order\Change as OrderChangeResource;
use M2E\OnBuy\Model\ResourceModel\Order\Item as OrderItemResource;
use M2E\OnBuy\Model\ResourceModel\Order\Note as OrderNoteResource;
use Magento\Framework\DB\Ddl\Table;

class OrderHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use HandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installOrderTable($setup);
        $this->installOrderItemTable($setup);
        $this->installOrderNoteTable($setup);
        $this->installOrderChangeTable($setup);
    }

    private function installOrderTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_ORDER);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_SITE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_CHANNEL_ORDER_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_RESERVATION_STATE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_RESERVATION_START_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_ORDER_STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_PURCHASE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_CHANNEL_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_PRICE_TOTAL,
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_PRICE_SUBTOTAL,
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_PRICE_DELIVERY,
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_PRICE_DISCOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_SALES_FEE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_EMAIL,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_PHONE,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_BILLING_ADDRESS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_PAYMENT_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_SHIPPING_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_SHIPPED_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_CANCELLED_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_FEE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addIndex('account_id', OrderResource::COLUMN_ACCOUNT_ID)
            ->addIndex('site_id', OrderResource::COLUMN_SITE_ID)
            ->addIndex('store_id', OrderResource::COLUMN_STORE_ID)
            ->addIndex('channel_order_id', OrderResource::COLUMN_CHANNEL_ORDER_ID)
            ->addIndex('buyer_email', OrderResource::COLUMN_BUYER_EMAIL)
            ->addIndex('buyer_name', OrderResource::COLUMN_BUYER_NAME)
            ->addIndex('price_subtotal', OrderResource::COLUMN_PRICE_SUBTOTAL)
            ->addIndex('purchase_date', OrderResource::COLUMN_PURCHASE_DATE)
            ->addIndex('shipped_date', OrderResource::COLUMN_SHIPPED_DATE)
            ->addIndex('magento_order_id', OrderResource::COLUMN_MAGENTO_ORDER_ID)
            ->addIndex(
                'magento_order_creation_failure',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE
            )
            ->addIndex(
                'magento_order_creation_fails_count',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT
            )
            ->addIndex(
                'magento_order_creation_latest_attempt_date',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE
            )
            ->addIndex('reservation_state', OrderResource::COLUMN_RESERVATION_STATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderItemTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_ORDER_ITEM);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderItemResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_RESERVED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CHANNEL_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_PURCHASED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_DISPATCHED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_SALE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_ORIGINAL_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_EXPECTED_DISPATCH_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_FEE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TRACKING_DETAILS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('product_sku', OrderItemResource::COLUMN_PRODUCT_SKU)
            ->addIndex('product_title', OrderItemResource::COLUMN_PRODUCT_TITLE)
            ->addIndex('order_id', OrderItemResource::COLUMN_ORDER_ID)
            ->addIndex('product_id', OrderItemResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderNoteTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_ORDER_NOTE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderNoteResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_NOTE,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderNoteResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderNoteResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('order_id', OrderNoteResource::COLUMN_ORDER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderChangeTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_ORDER_CHANGE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderChangeResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_ACTION,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PARAMS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATOR_TYPE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                Table::TYPE_DATETIME,
            )
            ->addColumn(
                OrderChangeResource::COLUMN_HASH,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                OrderChangeResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('action', OrderChangeResource::COLUMN_ACTION)
            ->addIndex('creator_type', OrderChangeResource::COLUMN_CREATOR_TYPE)
            ->addIndex('hash', OrderChangeResource::COLUMN_HASH)
            ->addIndex('order_id', OrderChangeResource::COLUMN_ORDER_ID)
            ->addIndex('processing_attempt_count', OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
