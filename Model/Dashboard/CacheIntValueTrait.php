<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Dashboard;

trait CacheIntValueTrait
{
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;

    private function getCachedValue(string $key, int $lifetime, callable $handler): int
    {
        /** @var int|null $cachedValue */
        if ($cachedValue = $this->cache->getValue($key)) {
            return $cachedValue;
        }

        $value = (int)$handler();
        $this->cache->setValue($key, $value, [], $lifetime);

        return $value;
    }
}
