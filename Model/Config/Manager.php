<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Config;

class Manager
{
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\Core\Model\Config\AdapterFactory $adapterFactory;
    private \M2E\Core\Model\Config\Adapter $adapter;

    public function __construct(
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\Core\Model\Config\AdapterFactory $adapterFactory
    ) {
        $this->cache = $cache;
        $this->adapterFactory = $adapterFactory;
    }

    public function get(string $group, string $key)
    {
        return $this->getAdapter()->get($group, $key);
    }

    public function set(string $group, string $key, $value): void
    {
        $this->getAdapter()->set($group, $key, $value);
    }

    public function getGroupValue(string $group, string $key)
    {
        return $this->getAdapter()->get($group, $key);
    }

    public function setGroupValue(string $group, string $key, $value): void
    {
        $this->getAdapter()->set($group, $key, $value);
    }

    // ----------------------------------------

    public function getAdapter(): \M2E\Core\Model\Config\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory->create(
                \M2E\OnBuy\Helper\Module::IDENTIFIER,
                $this->cache->getAdapter()
            );
        }

        return $this->adapter;
    }
}
