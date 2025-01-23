<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync;

class LockManager
{
    private \M2E\OnBuy\Model\Processing\Lock\Repository $processingLockRepository;

    public function __construct(
        \M2E\OnBuy\Model\Processing\Lock\Repository $processingLockRepository
    ) {
        $this->processingLockRepository = $processingLockRepository;
    }

    public function isExistByAccount(\M2E\OnBuy\Model\Account $account): bool
    {
        foreach ($account->getSites() as $site) {
            if ($this->isExistBySite($site)) {
                return true;
            }
        }

        return false;
    }

    public function isExistBySite(\M2E\OnBuy\Model\Site $site): bool
    {
        return $this->processingLockRepository->isExist(\M2E\OnBuy\Model\Site::LOCK_NICK, $site->getId());
    }
}
