<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider\Attributes;

class Processor
{
    /** @var \M2E\OnBuy\Model\Category\Dictionary\Attribute\ProductAttribute[] */
    private array $attributes;
    private \M2E\OnBuy\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\OnBuy\Model\Category\Attribute\Recommended\RetrieveValue $recommendedProcessor;
    private \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector;

    public function __construct(
        \M2E\OnBuy\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\OnBuy\Model\Category\Attribute\Recommended\RetrieveValue $recommendedProcessor,
        \M2E\OnBuy\Model\Product\DataProvider\Attributes\NotFoundAttributeDetector $notFoundAttributeDetector
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->recommendedProcessor = $recommendedProcessor;
        $this->notFoundAttributeDetector = $notFoundAttributeDetector;
    }

    /**
     * @param \M2E\OnBuy\Model\Product $listingProduct
     *
     * @return \M2E\OnBuy\Model\Product\DataProvider\Attributes\Item[]
     */
    public function getAttributes(
        \M2E\OnBuy\Model\Product $listingProduct
    ): array {
        $result = [];
        $this->initDictionaryProductAttributes($listingProduct);
        $attributes = $this->getDictionaryCategoryAttributes($listingProduct->getTemplateCategoryId());
        $magentoProduct = $listingProduct->getMagentoProduct();

        $this->notFoundAttributeDetector->clearMessages();
        $this->notFoundAttributeDetector->searchNotFoundAttributes($magentoProduct);

        foreach ($attributes as $attribute) {
            $this->processAttribute($attribute, $magentoProduct, $result);
        }

        $this->notFoundAttributeDetector->processNotFoundAttributes(
            $magentoProduct,
            $listingProduct->getListing()->getStoreId(),
            (string)__('Product')
        );

        return $result;
    }

    public function getWarningMessages(): array
    {
        return $this->notFoundAttributeDetector->getWarningMessages();
    }

    private function initDictionaryProductAttributes(\M2E\OnBuy\Model\Product $listingProduct): void
    {
        $dictionary = $listingProduct->getCategoryDictionary();
        foreach ($dictionary->getProductAttributes() as $attribute) {
            $this->attributes[$attribute->getId()] = $attribute;
        }
    }

    /**
     * @param int $categoryId
     *
     * @return \M2E\OnBuy\Model\Category\CategoryAttribute[]
     */
    private function getDictionaryCategoryAttributes(int $categoryId): array
    {
        return $this->attributeRepository->findByDictionaryId(
            $categoryId,
            [\M2E\OnBuy\Model\Category\CategoryAttribute::ATTRIBUTE_TYPE_PRODUCT]
        );
    }

    private function processAttribute(
        \M2E\OnBuy\Model\Category\CategoryAttribute $attribute,
        \M2E\OnBuy\Model\Magento\Product $magentoProduct,
        array &$result
    ): void {
        $dictionaryAttribute = $this->getDictionaryAttributeById($attribute->getAttributeId());
        if ($attribute->isValueModeNone() || !$dictionaryAttribute) {
            return;
        }

        $recommendedValue = $this->recommendedProcessor->retrieveValue(
            $attribute,
            $dictionaryAttribute,
            $magentoProduct
        );

        $this->handleRecommendedValue($recommendedValue, $result);
    }

    private function getDictionaryAttributeById(
        string $attributeId
    ): ?\M2E\OnBuy\Model\Category\Dictionary\Attribute\ProductAttribute {
        return $this->attributes[$attributeId] ?? null;
    }

    private function handleRecommendedValue(
        \M2E\OnBuy\Model\Category\Attribute\Recommended\Result $recommendedValue,
        array &$result
    ): void {
        if ($recommendedValue->isFail()) {
            $this->notFoundAttributeDetector->addWarningMessage($recommendedValue->getFailMessages());
        } else {
            $result[] = $this->createAttributeItem(
                $recommendedValue->getResult()
            );
        }
    }

    private function createAttributeItem(int $valueId): \M2E\OnBuy\Model\Product\DataProvider\Attributes\Item
    {
        return new \M2E\OnBuy\Model\Product\DataProvider\Attributes\Item($valueId);
    }
}
