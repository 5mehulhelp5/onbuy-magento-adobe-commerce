<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Account;

use M2E\OnBuy\Model\Account\Issue\ValidTokens;

class DeleteService
{
    private Repository $accountRepository;
    private \M2E\OnBuy\Model\Order\Log\Repository $orderLogRepository;
    private \M2E\OnBuy\Model\Listing\Log\Repository $listingLogRepository;
    private \M2E\OnBuy\Helper\Module\Exception $exceptionHelper;
    private \M2E\OnBuy\Helper\Data\Cache\Permanent $cache;
    private \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService;
    private \M2E\OnBuy\Model\Listing\DeleteService $listingDeleteService;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Processing\DeleteService $processingDeleteService;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;
    private \M2E\OnBuy\Model\Order\DeleteService $deleteService;

    public function __construct(
        Repository $accountRepository,
        \M2E\OnBuy\Model\Listing\DeleteService $listingDeleteService,
        \M2E\OnBuy\Model\Order\Log\Repository $orderLogRepository,
        \M2E\OnBuy\Helper\Module\Exception $exceptionHelper,
        \M2E\OnBuy\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Model\Processing\DeleteService $processingDeleteService,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Helper\Data\Cache\Permanent $cache,
        \M2E\OnBuy\Model\Order\DeleteService $deleteService
    ) {
        $this->accountRepository = $accountRepository;
        $this->orderLogRepository = $orderLogRepository;
        $this->listingLogRepository = $listingLogRepository;
        $this->exceptionHelper = $exceptionHelper;
        $this->cache = $cache;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->listingDeleteService = $listingDeleteService;
        $this->listingRepository = $listingRepository;
        $this->processingDeleteService = $processingDeleteService;
        $this->siteRepository = $siteRepository;
        $this->deleteService = $deleteService;
    }

    /**
     * @param \M2E\OnBuy\Model\Account $account
     *
     * @return void
     * @throws \Throwable
     */
    public function delete(\M2E\OnBuy\Model\Account $account): void
    {
        $accountId = $account->getId();

        // ---------------------------------------

        try {
            $this->orderLogRepository->removeByAccountId($accountId);

            $this->deleteService->deleteByAccountId($accountId);

            $this->listingLogRepository->removeByAccountId($accountId);

            $this->unmanagedProductDeleteService->deleteUnmanagedByAccountId($accountId);

            $this->removeListings($account);

            $this->deleteSites($account);

            $this->deleteAccount($account);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
            throw $e;
        }
    }

    private function removeListings(\M2E\OnBuy\Model\Account $account): void
    {
        foreach ($this->listingRepository->findForAccount($account) as $listing) {
            $this->listingDeleteService->process($listing);
        }
    }

    private function deleteSites(\M2E\OnBuy\Model\Account $account): void
    {
        foreach ($account->getSites() as $site) {
            $this->processingDeleteService->deleteByObjAndObjId(
                \M2E\OnBuy\Model\Site::LOCK_NICK,
                $site->getId()
            );

            $this->siteRepository->remove($site);
        }
    }

    private function deleteAccount(\M2E\OnBuy\Model\Account $account): void
    {
        $this->cache->removeTagValues('account');

        $this->accountRepository->remove($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }
}
