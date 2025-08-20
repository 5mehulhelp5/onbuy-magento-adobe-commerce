<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            'y25_m01' => [
                \M2E\OnBuy\Setup\Update\y25_m01\AddColumnsToProductWizard::class,
                \M2E\OnBuy\Setup\Update\y25_m01\AddConditionColumnsToListing::class,
                \M2E\OnBuy\Setup\Update\y25_m01\AddShippingPolicy::class,
            ],
            'y25_m03' => [
                \M2E\OnBuy\Setup\Update\y25_m03\RemoveOldCronValues::class,
                \M2E\OnBuy\Setup\Update\y25_m03\AddProductCreator::class,
                \M2E\OnBuy\Setup\Update\y25_m03\AddDescriptionTemplateTable::class,
                \M2E\OnBuy\Setup\Update\y25_m03\AddCategoryTables::class,
                \M2E\OnBuy\Setup\Update\y25_m03\AddTemplateCategoryToProduct::class
            ],
            'y25_m05' => [
                \M2E\OnBuy\Setup\Update\y25_m05\AddShippingReviseColumnToPolicy::class,
                \M2E\OnBuy\Setup\Update\y25_m05\AddOnlineDataColumnsToProduct::class
            ],
            'y25_m07' => [
                \M2E\OnBuy\Setup\Update\y25_m07\AddHandlingTime::class,
            ],
        ];
    }
}
