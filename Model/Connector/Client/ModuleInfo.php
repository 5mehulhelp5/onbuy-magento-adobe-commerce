<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Connector\Client;

class ModuleInfo implements \M2E\Core\Model\Connector\Client\ModuleInfoInterface
{
    private \M2E\OnBuy\Model\Module $module;

    public function __construct(\M2E\OnBuy\Model\Module $module)
    {
        $this->module = $module;
    }

    public function getName(): string
    {
        return $this->module->getName();
    }

    public function getVersion(): string
    {
        return $this->module->getPublicVersion();
    }
}
