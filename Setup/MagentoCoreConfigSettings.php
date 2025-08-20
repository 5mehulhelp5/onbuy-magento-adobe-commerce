<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup;

class MagentoCoreConfigSettings implements \M2E\Core\Model\Setup\MagentoCoreConfigSettingsInterface
{
    public const MAGENTO_CORE_CONFIG_PREFIX = 'm2e_onbuy';

    public function getConfigKeyPrefix(): string
    {
        return self::MAGENTO_CORE_CONFIG_PREFIX;
    }
}
