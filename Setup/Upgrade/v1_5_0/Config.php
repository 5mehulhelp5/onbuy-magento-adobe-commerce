<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Upgrade\v1_5_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\OnBuy\Setup\Update\y25_m07\AddHandlingTime::class,
        ];
    }
}
