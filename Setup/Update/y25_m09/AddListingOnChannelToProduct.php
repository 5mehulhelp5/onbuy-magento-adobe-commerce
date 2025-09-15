<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m09;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddListingOnChannelToProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL_START_DATE,
            Table::TYPE_DATETIME,
            'NULL',
            ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL,
            false,
            false
        );

        $modifier->commit();
    }
}
