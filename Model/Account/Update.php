<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Account;

use M2E\OnBuy\Model\Account\Issue\ValidTokens;

class Update
{
    private \M2E\OnBuy\Model\Channel\Connector\Account\Update\Processor $updateProcessor;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\OnBuy\Model\Site\UpdateService $siteUpdateService;
    private \M2E\OnBuy\Model\Channel\Connector\Site\GetList\Processor $siteGetListProcessor;

    public function __construct(
        \M2E\OnBuy\Model\Site\UpdateService $siteUpdateService,
        \M2E\OnBuy\Model\Channel\Connector\Account\Update\Processor $updateProcessor,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\OnBuy\Model\Channel\Connector\Site\GetList\Processor $siteGetListProcessor
    ) {
        $this->siteUpdateService = $siteUpdateService;
        $this->updateProcessor = $updateProcessor;
        $this->accountRepository = $accountRepository;
        $this->cache = $cache;
        $this->siteGetListProcessor = $siteGetListProcessor;
    }

    public function updateSettings(
        \M2E\OnBuy\Model\Account $account,
        string $title,
        \M2E\OnBuy\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\OnBuy\Model\Account\Settings\Order $orderSettings,
        \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): void {
        $account->setTitle($title)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        $this->accountRepository->save($account);
    }

    public function updateCredentials(\M2E\OnBuy\Model\Account $account, string $consumeKey, string $secretKey): void
    {
        $channelAccount = $this->updateProcessor->process(
            $account,
            $consumeKey,
            $secretKey,
        );

        $this->siteUpdateService->process($account, $channelAccount->sitesCollection);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }

    public function refresh(\M2E\OnBuy\Model\Account $account): void
    {
        $siteCollection = $this->siteGetListProcessor->get($account);
        $this->siteUpdateService->process($account, $siteCollection);
    }
}
