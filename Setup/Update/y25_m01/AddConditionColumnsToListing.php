<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m01;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Listing;

class AddConditionColumnsToListing extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->addColumn(
            Listing::COLUMN_CONDITION,
            'VARCHAR(255) NOT NULL',
            null,
            Listing::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            false,
            false
        );

        $modifier->addColumn(
            Listing::COLUMN_CONDITION_NOTE,
            'VARCHAR(255)',
            'NULL',
            Listing::COLUMN_CONDITION,
            false,
            false
        );

        $modifier->commit();
    }
}
