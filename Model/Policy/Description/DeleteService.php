<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Description;

class DeleteService extends \M2E\OnBuy\Model\Policy\AbstractDeleteService
{
    private \M2E\OnBuy\Model\Policy\Description\Repository $descriptionRepository;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Description\Repository $descriptionRepository,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository
    ) {
        $this->descriptionRepository = $descriptionRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\OnBuy\Model\Policy\PolicyInterface
    {
        return $this->descriptionRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingByDescriptionPolicy($policy->getId());
    }

    protected function delete(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): void
    {
        /** @var \M2E\OnBuy\Model\Policy\Description $policy */
        $this->descriptionRepository->delete($policy);
    }
}
