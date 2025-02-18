<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class SameSkuAlreadyExists implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\OnBuy\Model\Product\Repository $productRepository
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->productRepository = $productRepository;
    }

    public function validate(\M2E\OnBuy\Model\Product $product): ?string
    {
        $onBuyProductSku = $product->getOnlineSku();
        if (empty($onBuyProductSku)) {
            $onBuyProductSku = $product->getMagentoProduct()->getSku();
        }

        $existUnmanagedProduct = $this->unmanagedRepository->findBySkusAccountSite(
            [$onBuyProductSku],
            $product->getAccount()->getId(),
            $product->getListing()->getSiteId()
        );

        if (!empty($existUnmanagedProduct)) {
            return (string)__(
                'Product with the same SKU already exists in Unmanaged Items.
                 Once the Item is mapped to a Magento Product, it can be moved to an %extension_title Listing.',
                [
                    'extension_title' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                ]
            );
        }

        $existListProducts = $this->productRepository->findBySkusAccountSite(
            [$onBuyProductSku],
            $product->getAccount()->getId(),
            $product->getListing()->getSiteId()
        );

        //@todo to make sure it is a correct check
        if (!empty($existListProducts)) {
            $existListProduct = reset($existListProducts);
            if ($existListProduct->getId() !== $product->getId()) {
                return (string)__(
                    'Product with the same SKU already exists in your %listing_title Listing.',
                    [
                        'listing_title' => $existListProduct->getListing()->getTitle(),
                    ]
                );
            }
        }

        return null;
    }
}
