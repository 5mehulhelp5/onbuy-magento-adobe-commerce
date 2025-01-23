<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Connector\Client;

class Config implements \M2E\Core\Model\Connector\Client\ConfigInterface
{
    public const CONFIG_GROUP_SERVER = '/server/';
    public const CONFIG_KEY_APPLICATION_KEY = 'application_key';

    private \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig;
    private \M2E\Core\Model\LicenseService $licenseService;
    private \M2E\OnBuy\Model\Config\Manager $moduleConfig;

    public function __construct(
        \M2E\OnBuy\Model\Config\Manager $moduleConfig,
        \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        $this->connectorConfig = $connectorConfig;
        $this->licenseService = $licenseService;
        $this->moduleConfig = $moduleConfig;
    }

    public function getHost(): string
    {
        return $this->connectorConfig->getHost();
    }

    public function getConnectionTimeout(): int
    {
        return 15;
    }

    public function getTimeout(): int
    {
        return 300;
    }

    public function getApplicationKey(): string
    {
        return (string)$this->moduleConfig->get(self::CONFIG_GROUP_SERVER, self::CONFIG_KEY_APPLICATION_KEY);
    }

    public function getLicenseKey(): ?string
    {
        $license = $this->licenseService->get();

        return $license->hasKey() ? $license->getKey() : null;
    }
}
