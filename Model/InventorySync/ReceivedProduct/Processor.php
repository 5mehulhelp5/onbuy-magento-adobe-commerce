<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\InventorySync\ReceivedProduct;

class Processor
{
    private Repository $repository;
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedProductRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService;
    private \M2E\OnBuy\Model\InstructionService $instructionService;
    private \M2E\OnBuy\Model\Listing\LogService $logService;

    private int $logActionId;
    private \M2E\OnBuy\Model\InventorySync\ReceivedProductFactory $receivedProductFactory;

    public function __construct(
        Repository $repository,
        \M2E\OnBuy\Model\InventorySync\ReceivedProductFactory $receivedProductFactory,
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedProductRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService,
        \M2E\OnBuy\Model\InstructionService $instructionService,
        \M2E\OnBuy\Model\Listing\LogService $logService
    ) {
        $this->repository = $repository;
        $this->productRepository = $productRepository;
        $this->unmanagedProductRepository = $unmanagedProductRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->receivedProductFactory = $receivedProductFactory;
    }

    public function clear(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): void {
        $this->repository->removeAllByAccountAndSite($account->getId(), $site->getId());
    }

    public function collectReceivedProducts(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \M2E\OnBuy\Model\Channel\Product\ProductCollection $productCollection
    ): void {
        $receivedProducts = [];
        foreach ($productCollection->getAll() as $item) {
            $receivedProducts[] = $this->receivedProductFactory->create(
                $account->getId(),
                $site->getId(),
                $item->getSku()
            );
        }

        $this->repository->createBatch($receivedProducts);
    }

    public function processDeletedProducts(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $this->processNotReceivedProducts($account, $site, $inventorySyncProcessingStartDate);
        $this->removeNotReceivedUnmanagedProducts($account, $site);

        $this->repository
            ->removeAllByAccountAndSite($account->getId(), $site->getId());
    }

    private function processNotReceivedProducts(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $removedProducts = $this->productRepository->findRemovedFromChannel(
            $account->getId(),
            $site->getId(),
            $inventorySyncProcessingStartDate
        );

        foreach ($removedProducts as $product) {
            $product->setStatusNotListed(\M2E\OnBuy\Model\Product::STATUS_CHANGER_COMPONENT);

            $this->productRepository->save($product);

            $this->logService->addRecordToProduct(
                \M2E\OnBuy\Model\Listing\Log\Record::createSuccess(
                    (string)__('Product was deleted and is no longer available on the channel'),
                ),
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\OnBuy\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );

            $this->instructionService->create(
                $product->getId(),
                \M2E\OnBuy\Model\Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                'channel_changes_synchronization',
                80,
            );
        }
    }

    private function removeNotReceivedUnmanagedProducts(
        \M2E\OnBuy\Model\Account $account,
        \M2E\OnBuy\Model\Site $site
    ): void {
        $otherListings = $this->unmanagedProductRepository->findRemovedFromChannel(
            $account->getId(),
            $site->getId()
        );

        foreach ($otherListings as $other) {
            $this->unmanagedProductDeleteService->process($other);
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
