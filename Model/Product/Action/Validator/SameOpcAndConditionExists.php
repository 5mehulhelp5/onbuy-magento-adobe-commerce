<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class SameOpcAndConditionExists implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
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

    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        $existUnmanagedProducts = $this->unmanagedRepository->findByOpcAccountSite(
            [$product->getOpc()],
            $product->getAccount()->getId(),
            $product->getListing()->getSiteId()
        );

        foreach ($existUnmanagedProducts as $unmanagedProduct) {
            if (strtolower($unmanagedProduct->getCondition()) === $product->getListing()->getCondition()) {
                return (string)__(
                    'Product with the same OPC and Condition already exists in Unmanaged Items.'
                );
            }
        }

        $existListProducts = $this->productRepository->findByOpcAccountSite(
            [$product->getOpc()],
            $product->getAccount()->getId(),
            $product->getListing()->getSiteId()
        );

        foreach ($existListProducts as $existProduct) {
            if (
                $existProduct->getId() !== $product->getId()
                && $existProduct->getListing()->getCondition() === $product->getListing()->getCondition()
            ) {
                return (string)__(
                    'Product with the same OPC and condition already exists in your %listing_title Listing.',
                    [
                        'listing_title' => $existProduct->getListing()->getTitle(),
                    ]
                );
            }
        }

        return null;
    }
}
