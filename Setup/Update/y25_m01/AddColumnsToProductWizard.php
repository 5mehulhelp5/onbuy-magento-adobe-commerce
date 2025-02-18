<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m01;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product as ListingWizardProductResource;

class AddColumnsToProductWizard extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_WIZARD_PRODUCT);

        $modifier->addColumn(
            ListingWizardProductResource::COLUMN_CHANNEL_PRODUCT_ID,
            'VARCHAR(50)',
            'NULL',
            ListingWizardProductResource::COLUMN_CATEGORY_ID,
            true,
            false
        );

        $modifier->addColumn(
            ListingWizardProductResource::COLUMN_CHANNEL_PRODUCT_ID_SEARCH_STATUS,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            ListingWizardProductResource::COLUMN_CHANNEL_PRODUCT_ID,
            true,
            false
        );

        $modifier->addColumn(
            ListingWizardProductResource::COLUMN_CHANNEL_PRODUCT_DATA,
            'LONGTEXT',
            'NULL',
            ListingWizardProductResource::COLUMN_CHANNEL_PRODUCT_ID_SEARCH_STATUS,
            false,
            false
        );

        $modifier->commit();
    }
}
