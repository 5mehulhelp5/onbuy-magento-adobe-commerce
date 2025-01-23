<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

class DeleteService
{
    /** @var \M2E\OnBuy\Model\UnmanagedProduct\Repository */
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function process(\M2E\OnBuy\Model\UnmanagedProduct $unmanagedProduct): void
    {
        $this->repository->delete($unmanagedProduct);
    }

    public function deleteUnmanagedByAccountId(int $accountId): void
    {
        $this->repository->removeProductByAccount($accountId);
    }
}
