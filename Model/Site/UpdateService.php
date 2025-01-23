<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Site;

class UpdateService
{
    private \M2E\OnBuy\Model\SiteFactory $siteFactory;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\SiteFactory $siteFactory,
        \M2E\OnBuy\Model\Site\Repository $siteRepository
    ) {
        $this->siteFactory = $siteFactory;
        $this->siteRepository = $siteRepository;
    }

    public function process(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Channel\SiteCollection $channelSites
    ): void {
        $existSites = [];
        foreach ($account->getSites() as $site) {
            $existSites[$site->getSiteId()] = $site;
        }

        foreach ($channelSites->getAll() as $channelSite) {
            if (isset($existSites[$channelSite->id])) {
                continue;
            }

            $site = $this->siteFactory->create(
                $account,
                $channelSite->id,
                $channelSite->name,
                $channelSite->countryCode,
                $channelSite->currencyCode,
            );
            $this->siteRepository->create($site);

            $existSites[$site->getSiteId()] = $site;
        }

        $account->initSites(array_values($existSites));
    }
}
