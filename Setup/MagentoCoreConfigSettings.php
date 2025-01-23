<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup;

class MagentoCoreConfigSettings implements \M2E\Core\Model\Setup\MagentoCoreConfigSettingsInterface
{
    public function getConfigKeyPrefix(): string
    {
        return \M2E\OnBuy\Helper\Module\Database\Tables::PREFIX;
    }
}
