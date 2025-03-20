<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup\Upgrade\v1_1_2;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\OnBuy\Setup\Update\y25_m03\RemoveOldCronValues::class,
        ];
    }
}
