<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\InstallHandler;

use M2E\OnBuy\Helper\Module\Database\Tables as TablesHelper;
use M2E\OnBuy\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\OnBuy\Model\ResourceModel\Product\Lock as ProductLockResource;
use M2E\OnBuy\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;
use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;
use M2E\OnBuy\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\OnBuy\Model\ResourceModel\StopQueue as StopQueueResource;
use M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct as ReceivedProductResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class ProductHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use HandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProductTable($setup);
        $this->installProductInstructionTable($setup);
        $this->installProductScheduledActionTable($setup);
        $this->installStopQueueTable($setup);
        $this->installUnmanagedProductTable($setup);
        $this->installProductLockTable($setup);
        $this->installInventorySyncReceivedProductTable($setup);
    }

    private function installProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProductResource::COLUMN_ID,
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
                ProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductResource::COLUMN_CHANNEL_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_IS_PRODUCT_CREATOR,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_SKU,
                Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProductResource::COLUMN_STATUS_CHANGE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_MAIN_IMAGE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_ADDITIONAL_IMAGES,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_DELIVERY_TEMPLATE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_PRODUCT_URL,
                Table::TYPE_TEXT,
                250,
                ['nullable' => true]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ONLINE_GROUP_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_OPC,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_PRODUCT_ENCODED_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                ProductResource::COLUMN_IDENTIFIERS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addIndex(
                'status_change_date',
                [ProductResource::COLUMN_STATUS, ProductResource::COLUMN_STATUS_CHANGE_DATE]
            )
            ->addIndex('listing_id', ProductResource::COLUMN_LISTING_ID)
            ->addIndex('magento_product_id', ProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('channel_product_id', ProductResource::COLUMN_CHANNEL_PRODUCT_ID)
            ->addIndex('opc', ProductResource::COLUMN_OPC)
            ->addIndex('status', ProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_title', ProductResource::COLUMN_ONLINE_TITLE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductInstructionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProductInstructionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_SKIP_UNTIL,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_product_id', ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('type', ProductInstructionResource::COLUMN_TYPE)
            ->addIndex('priority', ProductInstructionResource::COLUMN_PRIORITY)
            ->addIndex('skip_until', ProductInstructionResource::COLUMN_SKIP_UNTIL)
            ->addIndex('create_date', ProductInstructionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductScheduledActionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);

        $productScheduledAction = $setup
            ->getConnection()
            ->newTable($tableName);

        $productScheduledAction
            ->addColumn(
                ScheduledActionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ACTION_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_IS_FORCE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'listing_product_id',
                [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('action_type', ScheduledActionResource::COLUMN_ACTION_TYPE)
            ->addIndex('tag', ScheduledActionResource::COLUMN_TAG)
            ->addIndex('create_date', ScheduledActionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productScheduledAction);
    }

    private function installStopQueueTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_STOP_QUEUE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                StopQueueResource::COLUMN_ID,
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
                StopQueueResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                StopQueueResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                StopQueueResource::COLUMN_SITE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                StopQueueResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                StopQueueResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                StopQueueResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('is_processed', StopQueueResource::COLUMN_IS_PROCESSED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installUnmanagedProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                UnmanagedProductResource::COLUMN_ID,
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
                UnmanagedProductResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_SITE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CHANNEL_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_PRODUCT_URL,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_GROUP_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_OPC,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_PRODUCT_ENCODED_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_IDENTIFIERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CURRENCY_CODE,
                Table::TYPE_TEXT,
                10,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_HANDLING_TIME,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CONDITIONS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CONDITIONS_NOTES,
                Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_DELIVERY_WEIGHT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_DELIVERY_TEMPLATE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex('account_id', UnmanagedProductResource::COLUMN_ACCOUNT_ID)
            ->addIndex('channel_product_id', UnmanagedProductResource::COLUMN_CHANNEL_PRODUCT_ID)
            ->addIndex('magento_product_id', UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('qty', UnmanagedProductResource::COLUMN_QTY)
            ->addIndex('status', UnmanagedProductResource::COLUMN_STATUS)
            ->addIndex('title', UnmanagedProductResource::COLUMN_TITLE)
            ->addIndex('opc', UnmanagedProductResource::COLUMN_OPC)
            ->addIndex('product_encoded_id', UnmanagedProductResource::COLUMN_PRODUCT_ENCODED_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductLockTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_LOCK);
        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProductLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                ProductLockResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProductLockResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('id', ProductLockResource::COLUMN_ID)
            ->addIndex('product_id', ProductLockResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installInventorySyncReceivedProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_INVENTORY_SYNC_RECEIVED_PRODUCT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ReceivedProductResource::COLUMN_ID,
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
                ReceivedProductResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ReceivedProductResource::COLUMN_SITE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ReceivedProductResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                50,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ReceivedProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ReceivedProductResource::COLUMN_ACCOUNT_ID)
            ->addIndex('site_id', ReceivedProductResource::COLUMN_SITE_ID)
            ->addIndex(
                'account_id_site_id',
                [ReceivedProductResource::COLUMN_ACCOUNT_ID, ReceivedProductResource::COLUMN_SITE_ID]
            )
            ->addIndex('sku', ReceivedProductResource::COLUMN_SKU)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    // ----------------------------------------

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }
}
