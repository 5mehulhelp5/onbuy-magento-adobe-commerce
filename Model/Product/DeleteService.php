<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

class DeleteService
{
    private \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;
    private \M2E\OnBuy\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\OnBuy\Model\Instruction\Repository $instructionRepository;
    private \M2E\OnBuy\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\OnBuy\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\OnBuy\Model\Instruction\Repository $instructionRepository,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->listingProductRepository = $listingProductRepository;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->instructionRepository = $instructionRepository;
        $this->listingLogService = $listingLogService;
    }

    public function process(
        \M2E\OnBuy\Model\Product $product,
        $initiator
    ): void {
        $this->removeTags($product);

        $this->removeScheduledActions($product);
        $this->removeInstructions($product);

        $this->listingLogService->addProduct(
            $product,
            $initiator,
            \M2E\OnBuy\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
            $this->listingLogService->getNextActionId(),
            (string)__('Product was Deleted'),
            \M2E\OnBuy\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->listingProductRepository->delete($product);
    }

    private function removeTags(\M2E\OnBuy\Model\Product $product): void
    {
        $this->tagBuffer->removeAllTags($product);
        $this->tagBuffer->flush();
    }

    private function removeScheduledActions(\M2E\OnBuy\Model\Product $product): void
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($product->getId());
        if ($scheduledAction !== null) {
            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function removeInstructions(\M2E\OnBuy\Model\Product $product): void
    {
        $this->instructionRepository->removeByListingProduct($product->getId());
    }
}
