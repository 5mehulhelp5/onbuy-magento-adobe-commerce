<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product;

class CreateService
{
    private \M2E\OnBuy\Model\ProductFactory $listingProductFactory;
    /** @var \M2E\OnBuy\Model\Product\Repository */
    private Repository $listingProductRepository;

    public function __construct(
        \M2E\OnBuy\Model\ProductFactory $listingProductFactory,
        Repository $listingProductRepository
    ) {
        $this->listingProductFactory = $listingProductFactory;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function create(
        \M2E\OnBuy\Model\Listing $listing,
        \M2E\OnBuy\Model\Magento\Product $m2eMagentoProduct,
        ?\M2E\OnBuy\Model\UnmanagedProduct $unmanagedProduct = null
    ): \M2E\OnBuy\Model\Product {
        $this->checkSupportedMagentoType($m2eMagentoProduct);

        $listingProduct = $this->listingProductFactory->create(
            $listing,
            $m2eMagentoProduct->getProductId(),
        );

        if ($unmanagedProduct !== null) {
            $listingProduct->fillFromUnmanagedProduct($unmanagedProduct);
        }

        $this->listingProductRepository->create($listingProduct);
        $this->listingProductRepository->save($listingProduct);

        return $listingProduct;
    }

    // ----------------------------------------

    private function checkSupportedMagentoType(\M2E\OnBuy\Model\Magento\Product $m2eMagentoProduct): void
    {
        if (!$this->isSupportedMagentoProductType($m2eMagentoProduct)) {
            throw new \M2E\OnBuy\Model\Exception\Logic(
                (string)__(
                    sprintf('Unsupported magento product type - %s', $m2eMagentoProduct->getTypeId()),
                ),
            );
        }
    }

    private function isSupportedMagentoProductType(\M2E\OnBuy\Model\Magento\Product $ourMagentoProduct): bool
    {
        return $ourMagentoProduct->isSimpleType()
            || $ourMagentoProduct->isConfigurableType();
    }
}
