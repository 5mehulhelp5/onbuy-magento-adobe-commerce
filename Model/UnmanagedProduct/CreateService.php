<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

class CreateService
{
    private \M2E\OnBuy\Model\UnmanagedProductFactory $productFactory;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $repository;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProductFactory $productFactory,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $repository
    ) {
        $this->productFactory = $productFactory;
        $this->repository = $repository;
    }

    public function create(
        \M2E\OnBuy\Model\Channel\Product $channelProduct
    ): \M2E\OnBuy\Model\UnmanagedProduct {
        $unmanagedProduct = $this->productFactory->createFromChannel($channelProduct);

        $this->repository->create($unmanagedProduct);

        return $unmanagedProduct;
    }
}
