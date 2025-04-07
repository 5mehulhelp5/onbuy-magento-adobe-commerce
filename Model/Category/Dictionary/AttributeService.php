<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

class AttributeService
{
    private \M2E\OnBuy\Model\Channel\Attribute\Processor $attributeGetProcessor;
    private \M2E\OnBuy\Model\Category\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\OnBuy\Model\Channel\Attribute\Processor $attributeGetProcessor,
        \M2E\OnBuy\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeGetProcessor = $attributeGetProcessor;
        $this->attributeFactory = $attributeFactory;
    }

    public function getCategoryDataFromServer(
        string $serverHash,
        int $siteId,
        int $categoryId
    ): \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response {
        return $this->attributeGetProcessor
            ->process($serverHash, $siteId, $categoryId);
    }

    public function getBrands(): array
    {
        return [];
    }

    public function getProductAttributes(
        \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): array {
        $productAttributes = [];
        foreach ($categoryData->getAttributes() as $responseAttribute) {
            $values = [];
            foreach ($responseAttribute->getValues() as $value) {
                $values[] = $this->attributeFactory->createValue(
                    $value['id'],
                    $value['name']
                );
            }

            $productAttributes[] = $this->attributeFactory->createProductAttribute(
                $responseAttribute->getId(),
                $responseAttribute->getName(),
                $responseAttribute->isRequired(),
                $values
            );
        }

        return $productAttributes;
    }

    public function getTotalProductAttributes(
        \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): int {
        $productAttributesCount = 0;

        foreach ($categoryData->getAttributes() as $attribute) {
            if ($attribute->isProductType()) {
                $productAttributesCount++;
            }
        }

        $productAttributesCount++; // +1 for brand attribute

        return $productAttributesCount;
    }

    public function getHasRequiredAttributes(
        \M2E\OnBuy\Model\Channel\Connector\Attribute\Get\Response $categoryData
    ): bool {
        foreach ($categoryData->getAttributes() as $attribute) {
            if ($attribute->isProductType() && $attribute->isRequired()) {
                return true;
            }
        }

        return false;
    }
}
