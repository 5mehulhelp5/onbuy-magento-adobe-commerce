<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class SiteFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Site
    {
        return $this->objectManager->create(Site::class);
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        int $siteId,
        string $name,
        string $countryCode,
        string $currencyCode
    ): Site {
        $model = $this->createEmpty();
        $model->create($account, $siteId, $name, $countryCode, $currencyCode);

        return $model;
    }
}
