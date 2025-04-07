<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

class CreateService
{
    private \M2E\OnBuy\Model\Category\Tree\Repository $categoryTreeRepository;
    private \M2E\OnBuy\Model\Category\DictionaryFactory $dictionaryFactory;
    private \M2E\OnBuy\Model\Category\Tree\PathBuilder $pathBuilder;
    private \M2E\OnBuy\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\DictionaryFactory $dictionaryFactory,
        \M2E\OnBuy\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\OnBuy\Model\Category\Tree\Repository $categoryTreeRepository,
        \M2E\OnBuy\Model\Category\Tree\PathBuilder $pathBuilder,
        \M2E\OnBuy\Model\Site\Repository $siteRepository
    ) {
        $this->dictionaryFactory = $dictionaryFactory;
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->pathBuilder = $pathBuilder;
        $this->categoryTreeRepository = $categoryTreeRepository;
        $this->siteRepository = $siteRepository;
    }

    public function create(
        string $serverHash,
        int $siteId,
        int $categoryId
    ): \M2E\OnBuy\Model\Category\Dictionary {
        $treeNode = $this->categoryTreeRepository
            ->getCategoryBySiteIdAndCategoryId($siteId, $categoryId);

        if ($treeNode === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Not found category tree');
        }
        $site = $this->siteRepository->get($siteId);
        $categoryData = $this->attributeService->getCategoryDataFromServer($serverHash, $site->getSiteId(), $categoryId);
        $productAttributes = $this->attributeService->getProductAttributes($categoryData);
        $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
        $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

        $dictionary = $this->dictionaryFactory->create()->create(
            $siteId,
            $categoryId,
            $this->pathBuilder->getPath($treeNode),
            $productAttributes,
            $categoryData->getRules(),
            $this->attributeService->getBrands(),
            $totalProductAttributes,
            $hasRequiredProductAttributes
        );

        $this->categoryDictionaryRepository->create($dictionary);

        return $dictionary;
    }
}
