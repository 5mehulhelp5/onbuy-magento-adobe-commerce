<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\UnmanagedProduct;

class UnmapDeletedProduct
{
    private Repository $unmanagedRepository;
    private MappingService $mappingService;

    public function __construct(
        Repository $otherRepository,
        MappingService $mappingService
    ) {
        $this->unmanagedRepository = $otherRepository;
        $this->mappingService = $mappingService;
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

        $unmanagedProducts = $this->unmanagedRepository->findProductByMagentoProduct($magentoProductId);
        if (!empty($unmanagedProducts)) {
            foreach ($unmanagedProducts as $unmanagedProduct) {
                $this->mappingService->unmapProduct($unmanagedProduct);
            }
        }
    }
}
