<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Synchronization;

class DeleteService extends \M2E\OnBuy\Model\Policy\AbstractDeleteService
{
    private \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationRepository;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationRepository,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository
    ) {
        $this->synchronizationRepository = $synchronizationRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\OnBuy\Model\Policy\PolicyInterface
    {
        return $this->synchronizationRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySyncPolicy($policy->getId());
    }

    protected function delete(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): void
    {
        /** @var \M2E\OnBuy\Model\Policy\Synchronization $policy */
        $this->synchronizationRepository->delete($policy);
    }
}
