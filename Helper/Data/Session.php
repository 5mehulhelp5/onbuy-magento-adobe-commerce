<?php

declare(strict_types=1);

namespace M2E\OnBuy\Helper\Data;

class Session
{
    private \M2E\Core\Model\Session\Adapter $adapter;
    private \M2E\Core\Model\Session\AdapterFactory $adapterFactory;

    public function __construct(
        \M2E\Core\Model\Session\AdapterFactory $adapterFactory
    ) {
        $this->adapterFactory = $adapterFactory;
    }

    // ----------------------------------------

    public function getValue($key, $clear = false)
    {
        return $this->getAdapter()->getValue($key, $clear);
    }

    public function setValue($key, $value): void
    {
        $this->getAdapter()->setValue($key, $value);
    }

    // ---------------------------------------

    public function getAllValues(): array
    {
        return $this->getAdapter()->getAllValues();
    }

    public function removeValue($key): void
    {
        $this->getAdapter()->removeValue($key);
    }

    public function removeAllValues(): void
    {
        $this->getAdapter()->removeAllValues();
    }

    private function getAdapter(): \M2E\Core\Model\Session\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create(
                \M2E\OnBuy\Helper\Module::IDENTIFIER
            );
        }

        return $this->adapter;
    }
}
