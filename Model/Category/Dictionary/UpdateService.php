<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

class UpdateService
{
    private \M2E\OnBuy\Model\Category\Dictionary\AttributeService $attributeService;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\AttributeService $attributeService,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository
    ) {
        $this->attributeService = $attributeService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function update(
        \M2E\OnBuy\Model\Category\Dictionary $dictionary
    ): void {
        $site = $dictionary->getSite();
        $siteId = $site->getSiteId();
        $categoryId = $dictionary->getCategoryId();
        $serverHash = $site->getAccount()->getServerHash();

        try {
            $categoryData = $this->attributeService->getCategoryDataFromServer($serverHash, $siteId, (int)$categoryId);
            $productAttributes = $this->attributeService->getProductAttributes($categoryData);
            $totalProductAttributes = $this->attributeService->getTotalProductAttributes($categoryData);
            $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);
            $dictionary->setProductAttributes($productAttributes);
            $dictionary->setCategoryRules($categoryData->getRules());
            $dictionary->setTotalProductAttributes($totalProductAttributes);
            $dictionary->setHasRequiredProductAttributes($hasRequiredProductAttributes);
            $dictionary->markCategoryAsValid();
        } catch (\M2E\OnBuy\Model\Exception\CategoryInvalid $exception) {
            $dictionary->markCategoryAsInvalid();
        }

        $this->categoryDictionaryRepository->save($dictionary);
    }
}
