<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

class Reset
{
    private DeleteService $deleteService;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        DeleteService $deleteService
    ) {
        $this->deleteService = $deleteService;
        $this->siteRepository = $siteRepository;
    }

    public function process(\M2E\OnBuy\Model\Account $account): void
    {
        $this->deleteService->deleteUnmanagedByAccountId($account->getId());
        $this->resetSitesInventoryLastSyncDate($account);
    }

    private function resetSitesInventoryLastSyncDate(\M2E\OnBuy\Model\Account $account): void
    {
        foreach ($account->getSites() as $site) {
            $site->resetInventoryLastSyncDate();
            $this->siteRepository->save($site);
        }
    }
}
