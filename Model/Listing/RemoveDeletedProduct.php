<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing;

class RemoveDeletedProduct
{
    private const INSTRUCTION_INITIATOR_DELETE_PRODUCT_FROM_MAGENTO = 'delete_product_from_magento';

    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\Product\DeleteService $productDeleteService;
    private \M2E\OnBuy\Model\Listing\LogService $listingLogService;
    private \M2E\OnBuy\Model\InstructionService $instructionService;
    private \M2E\OnBuy\Model\StopQueueService $stopQueueService;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\StopQueueService $stopQueueService,
        \M2E\OnBuy\Model\Product\DeleteService $productDeleteService,
        \M2E\OnBuy\Model\Listing\LogService $listingLogService,
        \M2E\OnBuy\Model\InstructionService $instructionService
    ) {
        $this->productRepository = $productRepository;
        $this->productDeleteService = $productDeleteService;
        $this->listingLogService = $listingLogService;
        $this->instructionService = $instructionService;
        $this->stopQueueService = $stopQueueService;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $magentoProduct
     *
     * @return void
     */
    public function process($magentoProduct): void
    {
        $magentoProductId = $magentoProduct instanceof \Magento\Catalog\Model\Product
            ? (int)$magentoProduct->getId()
            : (int)$magentoProduct;

        $this->processSimpleProducts($magentoProductId);
    }

    private function processSimpleProducts(int $magentoProductId): void
    {
        $listingsProducts = $this->productRepository
            ->findByMagentoProductId($magentoProductId);

        $processedListings = [];
        foreach ($listingsProducts as $listingProduct) {
            $message = (string)__('Item was deleted from Magento.');
            if (!$listingProduct->isStatusNotListed()) {
                $message = (string)__('Item was deleted from Magento and stopped on the Channel.');
            }

            if ($listingProduct->isRemovableFromChannel()) {
                $this->stopQueueService->add($listingProduct);
            }

            $listingProduct->setStatusInactive(\M2E\OnBuy\Model\Product::STATUS_CHANGER_USER);
            $this->productRepository->save($listingProduct);

            $this->productDeleteService->process($listingProduct, \M2E\Core\Helper\Data::INITIATOR_EXTENSION);

            $listingId = $listingProduct->getListingId();
            if (isset($processedListings[$listingId])) {
                continue;
            }

            $processedListings[$listingId] = true;

            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\OnBuy\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                null,
                $message,
                \M2E\OnBuy\Model\Log\AbstractModel::TYPE_WARNING,
            );
        }
    }

    private function addReviseInstruction(\M2E\OnBuy\Model\Product $product): void
    {
        $this->instructionService->create(
            $product->getId(),
            \M2E\OnBuy\Model\Product::INSTRUCTION_TYPE_VARIANT_SKU_REMOVED,
            self::INSTRUCTION_INITIATOR_DELETE_PRODUCT_FROM_MAGENTO,
            80,
        );
    }
}
