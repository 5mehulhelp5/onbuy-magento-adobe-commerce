<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Listing\Wizard;

class SearchChannelProductIdManager
{
    private const MAX_PRODUCT_COUNT_FOR_SEARCH = 20;

    private \M2E\OnBuy\Model\Product\Repository $productRepository;
    private \M2E\OnBuy\Model\Product\SearchChannelProductsService $searchProductService;
    private \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        \M2E\OnBuy\Model\Product\SearchChannelProductsService $searchProductService,
        \M2E\OnBuy\Model\Listing\Wizard\Repository $wizardRepository,
        \M2E\OnBuy\Model\Settings $settings
    ) {
        $this->productRepository = $productRepository;
        $this->searchProductService = $searchProductService;
        $this->wizardRepository = $wizardRepository;
        $this->settings = $settings;
    }

    public function isAllFound(\M2E\OnBuy\Model\Listing\Wizard\Manager $manager): bool
    {
        $allProductsIds = $manager->getProductsIds();
        if (empty($allProductsIds)) {
            return true;
        }

        $searchStatistic = $this->productRepository->getStatisticForSearchChannelId(
            $manager->getWizardId(),
            $allProductsIds,
        );

        return empty($searchStatistic[\M2E\OnBuy\Model\Listing\Wizard\Product::SEARCH_STATUS_NONE]);
    }

    public function find(\M2E\OnBuy\Model\Listing\Wizard\Manager $manager): ?SearchChannelProductManager\FindResult
    {
        $allProductsIds = $manager->getProductsIds();
        if (empty($allProductsIds)) {
            return null;
        }

        $products = $manager->findProductsForSearchChannelId(self::MAX_PRODUCT_COUNT_FOR_SEARCH);
        if (empty($products)) {
            return null;
        }

        ['skip' => $skipProducts, 'search' => $groupByIdentifierProducts] = $this->groupProductsForProcess($products);

        if (!empty($skipProducts)) {
            $this->processSkip($skipProducts);
        }

        if (empty($groupByIdentifierProducts)) {
            return new \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductManager\FindResult(
                $this->isAllFound($manager),
                count($allProductsIds),
                self::MAX_PRODUCT_COUNT_FOR_SEARCH
            );
        }

        $channelIdsGroupByIdentifier = $this->searchChannelIds(array_keys($groupByIdentifierProducts), $manager->getListing());

        $skipProducts = [];
        foreach ($groupByIdentifierProducts as $identifier => $products) {
            if (!isset($channelIdsGroupByIdentifier[$identifier])) {
                array_push($skipProducts, ...$products);

                continue;
            }

            /** @var \M2E\OnBuy\Model\Listing\Wizard\Product $product */
            foreach ($products as $product) {
                $product->setChannelProductId($channelIdsGroupByIdentifier[$identifier]['opc']);
                $product->setChannelProductData($channelIdsGroupByIdentifier[$identifier]);
                $this->wizardRepository->saveProduct($product);
            }
        }

        $this->processSkip($skipProducts);

        return new \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductManager\FindResult(
            $this->isAllFound($manager),
            count($allProductsIds),
            self::MAX_PRODUCT_COUNT_FOR_SEARCH
        );
    }

    private function groupProductsForProcess(array $products): array
    {
        $productsForSearchByIdentifier = [];
        $productsForSkip = [];

        $identifierAttributeCode = $this->settings->getIdentifierCodeValue();
        /** @var \M2E\OnBuy\Model\Listing\Wizard\Product $product */
        foreach ($products as $product) {
            $magentoProduct = $product->getMagentoProduct();
            $identifier = $magentoProduct->getAttributeValue($identifierAttributeCode);
            if (empty($identifier)) {
                $productsForSkip[] = $product;
                continue;
            }

            $productsForSearchByIdentifier[$identifier][] = $product;
        }

        return ['skip' => $productsForSkip, 'search' => $productsForSearchByIdentifier];
    }

    /**
     * @param \M2E\OnBuy\Model\Listing\Wizard\Product[] $skipProducts
     *
     * @return void
     */
    private function processSkip(array $skipProducts): void
    {
        foreach ($skipProducts as $product) {
            $product->markChannelIdIsSearched();

            $this->wizardRepository->saveProduct($product);
        }
    }

    private function searchChannelIds(array $identifiers, \M2E\OnBuy\Model\Listing $listing): array
    {
        $groupedProductIdsByIdentifier = [];

        try {
            $channelProducts = $this->searchProductService->findByIdentifiers(
                $listing->getAccount(),
                $listing->getSite(),
                $identifiers,
            );

            foreach ($channelProducts as $channelProduct) {
                $data = [
                    'identifier' => $channelProduct->getIdentifier(),
                    'opc' => $channelProduct->getOpc(),
                    'name' => $channelProduct->getName(),
                    'url' => $channelProduct->getUrl(),
                    'img' => $channelProduct->getImg(),
                ];
                $groupedProductIdsByIdentifier[$channelProduct->getIdentifier()] = $data;
            }
        } catch (\Throwable $e) {
        }

        return $groupedProductIdsByIdentifier;
    }
}
