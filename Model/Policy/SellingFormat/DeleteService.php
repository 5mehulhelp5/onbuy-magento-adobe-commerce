<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\SellingFormat;

class DeleteService extends \M2E\OnBuy\Model\Policy\AbstractDeleteService
{
    private \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatRepository,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository
    ) {
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\OnBuy\Model\Policy\PolicyInterface
    {
        return $this->sellingFormatRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySellingPolicy($policy->getId());
    }

    protected function delete(\M2E\OnBuy\Model\Policy\PolicyInterface $policy): void
    {
        /** @var \M2E\OnBuy\Model\Policy\SellingFormat $policy */
        $this->sellingFormatRepository->delete($policy);
    }
}
