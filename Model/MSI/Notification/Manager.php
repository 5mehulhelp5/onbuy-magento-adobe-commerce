<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\MSI\Notification;

class Manager
{
    public const REGISTRY_KEY = '/view/msi/popup/shown/';

    private \M2E\OnBuy\Model\Registry\Manager $registry;

    public function __construct(\M2E\OnBuy\Model\Registry\Manager $registry)
    {
        $this->registry = $registry;
    }

    public function isNeedShow(): bool
    {
        return !$this->registry->getValue(self::REGISTRY_KEY);
    }

    public function markAsShow(): void
    {
        $this->registry->setValue(self::REGISTRY_KEY, '1');
    }
}
