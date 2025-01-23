<?php

declare(strict_types=1);

namespace M2E\OnBuy\Helper\Module\Database;

class Tables
{
    public const PREFIX = 'm2e_onbuy_';

    public const TABLE_NAME_WIZARD = self::PREFIX . 'wizard';

    public const TABLE_NAME_ACCOUNT = self::PREFIX . 'account';
    public const TABLE_NAME_SITE = self::PREFIX . 'site';

    public const TABLE_NAME_LISTING = self::PREFIX . 'listing';
    public const TABLE_NAME_LISTING_LOG = self::PREFIX . 'listing_log';
    public const TABLE_NAME_LISTING_WIZARD = self::PREFIX . 'listing_wizard';
    public const TABLE_NAME_LISTING_WIZARD_STEP = self::PREFIX . 'listing_wizard_step';

    public const TABLE_NAME_LISTING_WIZARD_PRODUCT = self::PREFIX . 'listing_wizard_product';
    public const TABLE_NAME_PRODUCT = self::PREFIX . 'product';
    public const TABLE_NAME_PRODUCT_INSTRUCTION = self::PREFIX . 'product_instruction';
    public const TABLE_NAME_PRODUCT_SCHEDULED_ACTION = self::PREFIX . 'product_scheduled_action';
    public const TABLE_NAME_UNMANAGED_PRODUCT = self::PREFIX . 'unmanaged_product';

    public const TABLE_NAME_INVENTORY_SYNC_RECEIVED_PRODUCT = self::PREFIX . 'inventory_sync_received_product';
    public const TABLE_NAME_PRODUCT_LOCK = self::PREFIX . 'product_lock';

    public const TABLE_NAME_LOCK_ITEM = self::PREFIX . 'lock_item';
    public const TABLE_NAME_LOCK_TRANSACTIONAL = self::PREFIX . 'lock_transactional';
    public const TABLE_NAME_PROCESSING = self::PREFIX . 'processing';

    public const TABLE_NAME_PROCESSING_PARTIAL_DATA = self::PREFIX . 'processing_partial_data';

    public const TABLE_NAME_PROCESSING_LOCK = self::PREFIX . 'processing_lock';

    public const TABLE_NAME_STOP_QUEUE = self::PREFIX . 'stop_queue';
    public const TABLE_NAME_SYNCHRONIZATION_LOG = self::PREFIX . 'synchronization_log';
    public const TABLE_NAME_SYSTEM_LOG = self::PREFIX . 'system_log';

    public const TABLE_NAME_OPERATION_HISTORY = self::PREFIX . 'operation_history';
    public const TABLE_NAME_TEMPLATE_SELLING_FORMAT = self::PREFIX . 'template_selling_format';
    public const TABLE_NAME_TEMPLATE_SYNCHRONIZATION = self::PREFIX . 'template_synchronization';

    public const TABLE_NAME_TAG = self::PREFIX . 'tag';

    public const TABLE_NAME_PRODUCT_TAG_RELATION = self::PREFIX . 'product_tag_relation';

    public const TABLE_NAME_ORDER = self::PREFIX . 'order';
    public const TABLE_NAME_ORDER_ITEM = self::PREFIX . 'order_item';
    public const TABLE_NAME_ORDER_LOG = self::PREFIX . 'order_log';
    public const TABLE_NAME_ORDER_NOTE = self::PREFIX . 'order_note';

    public const TABLE_NAME_ORDER_CHANGE = self::PREFIX . 'order_change';

    /**
     * @return string[]
     */
    public static function getAllTables(): array
    {
        return array_keys(self::getTablesModels());
    }

    public static function getTableModel(string $tableName): ?string
    {
        $tablesModels = self::getTablesModels();

        return $tablesModels[$tableName] ?? null;
    }

    private static function getTablesModels(): array
    {
        return [
            self::TABLE_NAME_ACCOUNT => \M2E\OnBuy\Model\ResourceModel\Account::class,
            self::TABLE_NAME_SITE => \M2E\OnBuy\Model\ResourceModel\Site::class,
            self::TABLE_NAME_LISTING => \M2E\OnBuy\Model\ResourceModel\Listing::class,
            self::TABLE_NAME_LISTING_LOG => \M2E\OnBuy\Model\ResourceModel\Listing\Log::class,
            self::TABLE_NAME_LISTING_WIZARD => \M2E\OnBuy\Model\ResourceModel\Listing\Wizard::class,
            self::TABLE_NAME_LISTING_WIZARD_STEP => \M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Step::class,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT => \M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product::class,
            self::TABLE_NAME_PRODUCT => \M2E\OnBuy\Model\ResourceModel\Product::class,
            self::TABLE_NAME_PRODUCT_INSTRUCTION => \M2E\OnBuy\Model\ResourceModel\Instruction::class,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION => \M2E\OnBuy\Model\ResourceModel\ScheduledAction::class,
            self::TABLE_NAME_LOCK_ITEM => \M2E\OnBuy\Model\ResourceModel\Lock\Item::class,
            self::TABLE_NAME_LOCK_TRANSACTIONAL => \M2E\OnBuy\Model\ResourceModel\Lock\Transactional::class,
            self::TABLE_NAME_PROCESSING => \M2E\OnBuy\Model\ResourceModel\Processing::class,
            self::TABLE_NAME_PROCESSING_LOCK => \M2E\OnBuy\Model\ResourceModel\Processing\Lock::class,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA => \M2E\OnBuy\Model\ResourceModel\Processing\PartialData::class,
            self::TABLE_NAME_STOP_QUEUE => \M2E\OnBuy\Model\ResourceModel\StopQueue::class,
            self::TABLE_NAME_SYNCHRONIZATION_LOG => \M2E\OnBuy\Model\ResourceModel\Synchronization\Log::class,
            self::TABLE_NAME_SYSTEM_LOG => \M2E\OnBuy\Model\ResourceModel\Log\System::class,
            self::TABLE_NAME_OPERATION_HISTORY => \M2E\OnBuy\Model\ResourceModel\OperationHistory::class,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT => \M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat::class,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION => \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization::class,
            self::TABLE_NAME_WIZARD => \M2E\OnBuy\Model\ResourceModel\Wizard::class,
            self::TABLE_NAME_TAG => \M2E\OnBuy\Model\ResourceModel\Tag::class,
            self::TABLE_NAME_PRODUCT_TAG_RELATION => \M2E\OnBuy\Model\ResourceModel\Tag\ListingProduct\Relation::class,
            self::TABLE_NAME_ORDER => \M2E\OnBuy\Model\ResourceModel\Order::class,
            self::TABLE_NAME_ORDER_ITEM => \M2E\OnBuy\Model\ResourceModel\Order\Item::class,
            self::TABLE_NAME_ORDER_LOG => \M2E\OnBuy\Model\ResourceModel\Order\Log::class,
            self::TABLE_NAME_ORDER_NOTE => \M2E\OnBuy\Model\ResourceModel\Order\Note::class,
            self::TABLE_NAME_ORDER_CHANGE => \M2E\OnBuy\Model\ResourceModel\Order\Change::class,
            self::TABLE_NAME_UNMANAGED_PRODUCT => \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct::class,
            self::TABLE_NAME_INVENTORY_SYNC_RECEIVED_PRODUCT => \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::class,
        ];
    }

    // ----------------------------------------

    public static function isModuleTable(string $tableName): bool
    {
        return strpos($tableName, self::PREFIX) !== false;
    }
}
