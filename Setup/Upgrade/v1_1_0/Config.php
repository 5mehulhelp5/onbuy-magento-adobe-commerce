<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Upgrade\v1_1_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\OnBuy\Setup\Update\y25_m01\AddColumnsToProductWizard::class,
            \M2E\OnBuy\Setup\Update\y25_m01\AddConditionColumnsToListing::class,
            \M2E\OnBuy\Setup\Update\y25_m01\AddShippingPolicy::class,
        ];
    }
}
