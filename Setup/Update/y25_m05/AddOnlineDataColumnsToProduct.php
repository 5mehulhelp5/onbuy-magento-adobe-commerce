<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m05;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;

class AddOnlineDataColumnsToProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_DESCRIPTION,
            'VARCHAR(255)',
            'NULL',
            ProductResource::COLUMN_ONLINE_TITLE,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_MAIN_IMAGE,
            'VARCHAR(255)',
            'NULL',
            ProductResource::COLUMN_ONLINE_DESCRIPTION,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_ADDITIONAL_IMAGES,
            'VARCHAR(255)',
            'NULL',
            ProductResource::COLUMN_ONLINE_MAIN_IMAGE,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_CATEGORY_ID,
            'INT UNSIGNED',
            'NULL',
            ProductResource::COLUMN_ONLINE_ADDITIONAL_IMAGES,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
            'VARCHAR(255)',
            'NULL',
            ProductResource::COLUMN_ONLINE_CATEGORY_ID,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_DELIVERY_TEMPLATE_ID,
            'INT UNSIGNED',
            'NULL',
            ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA,
            false,
            false
        );

        $modifier->commit();
    }
}
