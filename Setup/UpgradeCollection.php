<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup;

class UpgradeCollection extends \M2E\Core\Model\Setup\AbstractUpgradeCollection
{
    public function getMinAllowedVersion(): string
    {
        return '1.0.0';
    }

    protected function getSourceVersionUpgrades(): array
    {
        return [
            //'from_version1' => [
            //    'to' => '$version$',
            //    'upgrade' => null or UpgradeConfig class name, \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
            // ],
        ];
    }
}
