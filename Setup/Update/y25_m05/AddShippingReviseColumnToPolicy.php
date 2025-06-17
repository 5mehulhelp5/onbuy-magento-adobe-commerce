<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m05;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Policy\Synchronization as SynchronizationResource;

class AddShippingReviseColumnToPolicy extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);

        $modifier->addColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_SHIPPING,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
            false,
            false
        );

        $modifier->commit();

        $modifier->changeColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_SHIPPING,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
            false,
        );

        $modifier->commit();
    }
}
