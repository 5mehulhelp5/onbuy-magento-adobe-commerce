<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Shipping;

class ShippingService
{
    private const CACHE_KEY_DELIVERY_TEMPLATES = 'shipping_delivery_templates';
    private const CACHE_LIFETIME_THIRTY_MINUTES = 1800;

    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplateService $deliveryTemplateService;

    public function __construct(
        \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplateService $deliveryTemplateService,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache
    ) {
        $this->cache = $cache;
        $this->deliveryTemplateService = $deliveryTemplateService;
    }

    public function getAllDeliveryTemplates(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        bool $force
    ): \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection {
        if (!$force) {
            $cachedData = $this->fromCache($this->createCacheKey($account, $site));
            if ($cachedData !== null) {
                return \M2E\OnBuy\Model\Channel\Policy\Shipping\DeliveryTemplate\Collection::createFromArray($cachedData);
            }
        }

        $this->clearCache($this->createCacheKey($account, $site));

        $deliveryTemplateCollection = $this->deliveryTemplateService->retrieve($account, $site);
        if ($deliveryTemplateCollection->isEmpty()) {
            return $deliveryTemplateCollection;
        }

        $this->toCache(
            $deliveryTemplateCollection->toArray(),
            $this->createCacheKey($account, $site)
        );

        return $deliveryTemplateCollection;
    }

    // ----------------------------------------

    private function createCacheKey(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): string {
        return self::CACHE_KEY_DELIVERY_TEMPLATES . $account->getId() . $site->getId();
    }

    private function toCache(array $data, string $key): void
    {
        $this->cache->setValue($key, $data, [], self::CACHE_LIFETIME_THIRTY_MINUTES);
    }

    private function fromCache(string $key): ?array
    {
        return $this->cache->getValue($key);
    }

    private function clearCache(string $key): void
    {
        $this->cache->removeValue($key);
    }
}
