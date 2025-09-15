<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Wizard;

class CompleteProcessor
{
    private \M2E\OnBuy\Model\Listing\AddProductsService $addProductsService;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService;
    private \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\OnBuy\Model\Product\Category\Attribute\ValidateManager $productAttributeValidateManager;

    public function __construct(
        \M2E\OnBuy\Model\Listing\AddProductsService $addProductsService,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\DeleteService $unmanagedProductDeleteService,
        \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository,
        \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\OnBuy\Model\Product\Category\Attribute\ValidateManager $productAttributeValidateManager
    ) {
        $this->addProductsService = $addProductsService;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->wizardRepository = $wizardRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->productAttributeValidateManager = $productAttributeValidateManager;
    }

    public function process(Manager $wizardManager): array
    {
        $listing = $wizardManager->getListing();

        if (!$wizardManager->isEnabledCreateNewProductMode()) {
            $this->markAsProcessNotValidProducts($wizardManager);
        }

        $processedWizardProductIds = [];
        $listingProducts = [];
        foreach ($wizardManager->getNotProcessedProducts() as $wizardProduct) {
            $listingProduct = null;

            $processedWizardProductIds[] = $wizardProduct->getId();

            $magentoProduct = $this->magentoProductFactory->create()->setProductId($wizardProduct->getMagentoProductId());
            if (!$magentoProduct->exists()) {
                continue;
            }

            if ($wizardManager->isWizardTypeGeneral()) {
                $channelProductId = null;
                if ($wizardProduct->getChannelProductId()) {
                    $channelProductId = $wizardProduct->getChannelProductId();
                }

                $data = $wizardProduct->getChannelProductData();
                $listingProduct = $this->addProductsService
                    ->addProduct(
                        $listing,
                        $magentoProduct,
                        $wizardProduct->getCategoryDictionaryId(),
                        $channelProductId,
                        $data['url'] ?? null,
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );
            } elseif ($wizardManager->isWizardTypeUnmanaged()) {
                $unmanagedProduct = $this->listingOtherRepository->findById($wizardProduct->getUnmanagedProductId());
                if ($unmanagedProduct === null) {
                    continue;
                }

                if (!$unmanagedProduct->getMagentoProduct()->exists()) {
                    continue;
                }

                $listingProduct = $this->addProductsService
                    ->addFromUnmanaged(
                        $listing,
                        $unmanagedProduct,
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );

                $this->unmanagedProductDeleteService->process($unmanagedProduct);
            }

            if ($listingProduct === null) {
                continue;
            }

            if ($wizardProduct->isInvalidCategoryAttributes()) {
                $this->productAttributeValidateManager->markProductAsNotValid(
                    $listingProduct,
                    $wizardProduct->getCategoryAttributesErrors()
                );
            } else {
                $this->productAttributeValidateManager->markProductAsValid($listingProduct);
            }

            $listingProducts[] = $listingProduct;

            if (count($processedWizardProductIds) % 100 === 0) {
                $wizardManager->markProductsAsProcessed($processedWizardProductIds);
                $processedWizardProductIds = [];
            }
        }

        if (!empty($processedWizardProductIds)) {
            $wizardManager->markProductsAsProcessed($processedWizardProductIds);
        }

        return $listingProducts;
    }

    private function markAsProcessNotValidProducts(Manager $wizardManager): void
    {
        $ids = $this->wizardRepository->getNotValidWizardProductsIds($wizardManager->getWizardId());
        if (!empty($ids)) {
            $this->wizardRepository->markProductsAsCompleted($wizardManager->getWizard(), $ids);
        }
    }
}
