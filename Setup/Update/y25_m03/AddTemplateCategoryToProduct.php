<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Update\y25_m03;

use M2E\OnBuy\Helper\Module\Database\Tables;
use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;

class AddTemplateCategoryToProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
            'INT UNSIGNED',
            null,
            ProductResource::COLUMN_IDENTIFIERS,
            true,
            false
        );

        $modifier->commit();
    }
}
