<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m07;

use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;
use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Policy\Shipping as ShippingResource;

class AddHandlingTime extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SHIPPING);

        $modifier->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME,
            'SMALLINT UNSIGNED',
            null,
            ShippingResource::COLUMN_DELIVERY_TEMPLATE_ID,
            false,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
            'SMALLINT UNSIGNED',
            0,
            ShippingResource::COLUMN_HANDLING_TIME,
            false,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
            'VARCHAR(255)',
            null,
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
            false,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_HANDLING_TIME,
            'SMALLINT UNSIGNED',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
