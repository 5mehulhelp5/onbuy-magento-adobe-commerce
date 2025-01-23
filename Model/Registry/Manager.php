<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Registry;

class Manager
{
    private \M2E\Core\Model\Registry\Adapter $adapter;
    private \M2E\Core\Model\Registry\AdapterFactory $adapterFactory;

    public function __construct(
        \M2E\Core\Model\Registry\AdapterFactory $adapterFactory
    ) {
        $this->adapterFactory = $adapterFactory;
    }

    // ----------------------------------------

    public function setValue(string $key, string $value): void
    {
        $this->getAdapter()->set($key, $value);
    }

    public function getValue(string $key)
    {
        return $this->getAdapter()->get($key);
    }

    public function getValueFromJson(string $key): array
    {
        $value = $this->getValue($key);
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }

    public function deleteValue(string $key): void
    {
        $this->getAdapter()->delete($key);
    }

    // ----------------------------------------

    public function getAdapter(): \M2E\Core\Model\Registry\Adapter
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
