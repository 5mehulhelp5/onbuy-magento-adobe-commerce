<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

use M2E\OnBuy\Model\Channel\Product\ProductCollection as ChannelProductCollection;

class UpdateFromChannel
{
    private Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\MappingService $mappingService;
    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedDeleteService;
    private \M2E\OnBuy\Model\UnmanagedProduct\CreateService $unmanagedCreateService;
    private \M2E\OnBuy\Model\Site $site;

    public function __construct(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\MappingService $mappingService,
        \M2E\OnBuy\Model\UnmanagedProduct\CreateService $unmanagedCreateService,
        \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedDeleteService
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->listingProductRepository = $listingProductRepository;
        $this->account = $account;
        $this->unmanagedCreateService = $unmanagedCreateService;
        $this->unmanagedDeleteService = $unmanagedDeleteService;
        $this->site = $site;
    }

    public function process(ChannelProductCollection $channelProductCollection): ?ChannelProductCollection
    {
        if ($channelProductCollection->empty()) {
            return null;
        }

        $existProductCollection = $this->removeExistInListingProduct($channelProductCollection);

        $this->processExist($channelProductCollection);
        $unmanagedItems = $this->processNew($channelProductCollection);

        $this->autoMapping($unmanagedItems);

        return $existProductCollection;
    }

    private function removeExistInListingProduct(
        ChannelProductCollection $channelProductCollection
    ): ChannelProductCollection {
        $existInProductCollection = new ChannelProductCollection();
        if ($channelProductCollection->empty()) {
            return $existInProductCollection;
        }

        $existedSkus = $this->listingProductRepository->findExistedSkusForAnyProductStatusBySkus(
            $channelProductCollection->getProductsSku(),
            $this->account->getId(),
            $this->site->getId(),
        );

        foreach ($existedSkus as $existedSku) {
            if (!$channelProductCollection->has($existedSku)) { // fix for duplicate products
                continue;
            }

            $existInProductCollection->add($channelProductCollection->get($existedSku));

            $channelProductCollection->remove($existedSku);
        }

        return $existInProductCollection;
    }

    private function processExist(ChannelProductCollection $channelProductCollection): void
    {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existProducts = $this->unmanagedRepository->findBySkusAccountSite(
            $channelProductCollection->getProductsSku(),
            $this->account->getId(),
            $this->site->getId()
        );

        foreach ($existProducts as $existProduct) {
            if (!$channelProductCollection->has($existProduct->getSku())) {
                continue;
            }

            $channelProduct = $channelProductCollection->get($existProduct->getSku());

            $channelProductCollection->remove($existProduct->getSku());

            if ($existProduct->updateFromChannel($channelProduct)) {
                $this->unmanagedRepository->save($existProduct);
            }
        }
    }

    /**
     * @param \M2E\OnBuy\Model\Channel\Product\ProductCollection $channelProductCollection
     *
     * @return \M2E\OnBuy\Model\UnmanagedProduct[]
     */
    private function processNew(ChannelProductCollection $channelProductCollection): array
    {
        $result = [];
        foreach ($channelProductCollection->getAll() as $item) {
            $unmanaged = $this->unmanagedCreateService->create($item);

            $result[] = $unmanaged;
        }

        return $result;
    }

    /**
     * @param \M2E\OnBuy\Model\UnmanagedProduct[] $unmanagedListings
     */
    private function autoMapping(array $unmanagedListings): void
    {
        $this->mappingService->autoMapUnmanagedProducts($unmanagedListings);
    }
}
