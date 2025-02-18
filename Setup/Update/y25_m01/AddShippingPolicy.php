<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m01;

use M2E\OnBuy\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;
use M2E\OnBuy\Model\ResourceModel\Policy\Shipping as ShippingResource;
use M2E\OnBuy\Model\ResourceModel\Listing as ListingResource;

class AddShippingPolicy extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createTableTemplateShipping();
        $this->modifyListing();
    }

    private function createTableTemplateShipping(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SHIPPING));

        $table
            ->addColumn(
                ShippingResource::COLUMN_ID,
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
                ShippingResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ShippingResource::COLUMN_SITE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ShippingResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ShippingResource::COLUMN_DELIVERY_TEMPLATE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ShippingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ShippingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($table);
    }

    private function modifyListing(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_LISTING);
        $modifier->addColumn(
            ListingResource::COLUMN_TEMPLATE_SHIPPING_ID,
            'INT UNSIGNED',
            'NULL',
            ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            true,
            false
        );

        $modifier->commit();
    }
}
