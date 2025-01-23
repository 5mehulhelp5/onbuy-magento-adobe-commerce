<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Helper\Module\Database\Tables as ModuleTablesHelper;

class Module implements \M2E\Core\Model\ModuleInterface
{
    private bool $areImportantTablesExist;

    private \M2E\Core\Model\Module\Adapter $adapter;
    private \M2E\Core\Model\Module\AdapterFactory $adapterFactory;
    private \M2E\OnBuy\Model\Registry\Manager $registryManager;
    private Config\Manager $configManager;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper;
    private \M2E\OnBuy\Helper\View\OnBuy $viewHelper;

    public function __construct(
        \M2E\Core\Model\Module\AdapterFactory $adapterFactory,
        Registry\Manager $registryManager,
        Config\Manager $configManager,
        \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\OnBuy\Helper\View\OnBuy $viewHelper
    ) {
        $this->registryManager = $registryManager;
        $this->adapterFactory = $adapterFactory;
        $this->configManager = $configManager;
        $this->moduleDatabaseHelper = $moduleDatabaseHelper;
        $this->resourceConnection = $resourceConnection;
        $this->viewHelper = $viewHelper;
    }

    public function getName(): string
    {
        return 'onbuy-m2';
    }

    public function getPublicVersion(): string
    {
        return $this->getAdapter()->getPublicVersion();
    }

    public function getSetupVersion(): string
    {
        return $this->getAdapter()->getSetupVersion();
    }

    public function getSchemaVersion(): string
    {
        return $this->getAdapter()->getSchemaVersion();
    }

    public function getDataVersion(): string
    {
        return $this->getAdapter()->getDataVersion();
    }

    public function hasLatestVersion(): bool
    {
        return $this->getAdapter()->hasLatestVersion();
    }

    public function setLatestVersion(string $version): void
    {
        $this->getAdapter()->setLatestVersion($version);
    }

    public function getLatestVersion(): ?string
    {
        return $this->getAdapter()->getLatestVersion();
    }

    public function isDisabled(): bool
    {
        return $this->getAdapter()->isDisabled();
    }

    public function disable(): void
    {
        $this->getAdapter()->disable();
    }

    public function enable(): void
    {
        $this->getAdapter()->enable();
    }

    public function isReadyToWork(): bool
    {
        return $this->areImportantTablesExist()
            && $this->viewHelper->isInstallationWizardFinished();
    }

    public function areImportantTablesExist(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->areImportantTablesExist)) {
            return $this->areImportantTablesExist;
        }

        $result = true;
        foreach ([ModuleTablesHelper::TABLE_NAME_WIZARD] as $table) {
            $tableName = $this->moduleDatabaseHelper->getTableNameWithPrefix($table);
            if (!$this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $result = false;
                break;
            }
        }

        return $this->areImportantTablesExist = $result;
    }

    public function getAdapter(): \M2E\Core\Model\Module\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create(
                \M2E\Core\Helper\Module::IDENTIFIER,
                $this->registryManager->getAdapter(),
                $this->configManager->getAdapter()
            );
        }

        return $this->adapter;
    }
}
