<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Module;

class Environment implements \M2E\Core\Model\Module\EnvironmentInterface
{
    private \M2E\Core\Model\Module\Environment\AdapterFactory $adapterFactory;

    private \M2E\Core\Model\Module\Environment\Adapter $adapter;
    private \M2E\OnBuy\Model\Config\Manager $configManager;

    public function __construct(
        \M2E\Core\Model\Module\Environment\AdapterFactory $adapterFactory,
        \M2E\OnBuy\Model\Config\Manager $configManager
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->configManager = $configManager;
    }

    public function isProductionEnvironment(): bool
    {
        return $this->getAdapter()->isProductionEnvironment();
    }

    public function isDevelopmentEnvironment(): bool
    {
        return $this->getAdapter()->isDevelopmentEnvironment();
    }

    public function enableProductionEnvironment(): void
    {
        $this->getAdapter()->enableProductionEnvironment();
    }

    public function enableDevelopmentEnvironment(): void
    {
        $this->getAdapter()->enableDevelopmentEnvironment();
    }

    public function getAdapter(): \M2E\Core\Model\Module\Environment\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create($this->configManager->getAdapter());
        }

        return $this->adapter;
    }
}
