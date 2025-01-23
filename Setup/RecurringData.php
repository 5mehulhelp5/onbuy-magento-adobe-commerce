<?php

declare(strict_types=1);

namespace M2E\OnBuy\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    private const MINIMUM_REQUIRED_MAGENTO_VERSION = '2.4.0';

    private \M2E\OnBuy\Helper\Module\Maintenance $maintenance;
    private \M2E\Core\Model\Setup\InstallChecker $installChecker;
    private \Magento\Framework\App\ProductMetadataInterface $productMetadata;
    private \M2E\Core\Model\Setup\InstallerFactory $installerFactory;
    private \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory;
    private InstallHandlerCollection $installHandlerCollection;
    private InstallTablesListResolver $tablesList;
    private UpgradeCollection $upgradeCollection;

    public function __construct(
        \M2E\OnBuy\Helper\Module\Maintenance $maintenance,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \M2E\Core\Model\Setup\InstallChecker $installChecker,
        \M2E\Core\Model\Setup\InstallerFactory $installerFactory,
        InstallHandlerCollection $installHandlerCollection,
        InstallTablesListResolver $tablesList,
        \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory,
        UpgradeCollection $upgradeCollection
    ) {
        $this->maintenance = $maintenance;
        $this->installChecker = $installChecker;
        $this->installerFactory = $installerFactory;
        $this->productMetadata = $productMetadata;
        $this->installHandlerCollection = $installHandlerCollection;
        $this->tablesList = $tablesList;
        $this->upgraderFactory = $upgraderFactory;
        $this->upgradeCollection = $upgradeCollection;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->checkMagentoVersion($this->productMetadata->getVersion());

        if (!$this->installChecker->isInstalled(\M2E\OnBuy\Helper\Module::IDENTIFIER)) {
            $this->installerFactory
                ->create(
                    \M2E\OnBuy\Helper\Module::IDENTIFIER,
                    $this->installHandlerCollection,
                    $this->tablesList,
                    $setup,
                    $this->maintenance
                )
                ->install();

            return;
        }

        // ----------------------------------------

        $this->upgraderFactory
            ->create(
                \M2E\OnBuy\Helper\Module::IDENTIFIER,
                $this->upgradeCollection,
                $setup,
                $this->maintenance
            )
            ->upgrade();
    }

    private function checkMagentoVersion(string $magentoVersion): void
    {
        if (!version_compare($magentoVersion, self::MINIMUM_REQUIRED_MAGENTO_VERSION, '>=')) {
            $this->maintenance->enableDueLowMagentoVersion();

            $message = sprintf(
                'Magento version %s is not compatible with M2E Extension.',
                $magentoVersion,
            );

            $message .= ' Please upgrade your Magento first.';

            throw new \RuntimeException($message);
        }
    }
}
