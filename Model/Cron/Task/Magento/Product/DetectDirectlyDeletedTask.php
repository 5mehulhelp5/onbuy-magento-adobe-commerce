<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Cron\Task\Magento\Product;

class DetectDirectlyDeletedTask extends \M2E\OnBuy\Model\Cron\AbstractTask
{
    public const NICK = 'magento/product/detect_directly_deleted';

    private \M2E\OnBuy\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct;
    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $otherRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $otherRepository,
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct,
        \M2E\OnBuy\Model\Cron\Manager $cronManager,
        \M2E\OnBuy\Model\Synchronization\LogService $syncLogger,
        \M2E\OnBuy\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\OnBuy\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\OnBuy\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource
        );
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->otherRepository = $otherRepository;
        $this->listingRemoveDeletedProduct = $listingRemoveDeletedProduct;
        $this->productRepository = $productRepository;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $processedIds = [];
        foreach ($this->productRepository->findRemovedMagentoProductIds(100) as $magentoProductId) {
            if (isset($processedIds[$magentoProductId])) {
                continue;
            }

            $processedIds[$magentoProductId] = true;

            $this->listingRemoveDeletedProduct->process($magentoProductId);
        }

        foreach ($this->otherRepository->findRemovedMagentoProductIds() as $magentoProductId) {
            $this->unmanagedUnmapDeletedProduct->process($magentoProductId);
        }
    }
}
