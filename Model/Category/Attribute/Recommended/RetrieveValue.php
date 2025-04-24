<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Attribute\Recommended;

use M2E\OnBuy\Model\Category\Dictionary\Attribute\Value;

class RetrieveValue
{
    public function retrieveValue(
        \M2E\OnBuy\Model\Category\CategoryAttribute $attribute,
        \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $dictionaryAttribute,
        \M2E\OnBuy\Model\Magento\Product $magentoProduct
    ): ?Result {
        switch ($attribute->getValueMode()) {
            case \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_RECOMMENDED:
                $result = $this->handleRecommendedMode($attribute, $dictionaryAttribute);
                break;
            case \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_VALUE:
                $result = $this->processValue(
                    $attribute->getCustomValue(),
                    $attribute->getAttributeName(),
                    $dictionaryAttribute
                );
                break;
            case \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE:
                $attributeVal = $magentoProduct->getAttributeValue($attribute->getCustomAttributeValue());
                $result = $this->processValue(
                    $attributeVal,
                    $attribute->getAttributeName(),
                    $dictionaryAttribute
                );
                break;
            default:
                $result = $this->processFail($dictionaryAttribute, $attribute->getAttributeName());
        }

        return $result;
    }

    private function handleRecommendedMode(
        \M2E\OnBuy\Model\Category\CategoryAttribute $attribute,
        \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $dictionaryAttribute
    ): Result {
        $result = null;
        foreach ($attribute->getRecommendedValue() as $valueId) {
            $result = (int)$valueId;
            break;
        }

        return $result
            ? Result::createSuccess($result)
            : $this->processFail($dictionaryAttribute, $attribute->getAttributeName());
    }

    private function processValue(
        string $attributeVal,
        string $attributeName,
        \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $attribute
    ): Result {
        $recommended = $this->findRecommendedIdByName($attribute->getValues(), $attributeVal);
        if ($recommended) {
            return Result::createSuccess($recommended);
        }

        return $this->processFail($attribute, $attributeName);
    }

    /**
     * @param Value[] $values
     * @param string $name
     *
     * @return int|null
     */
    private function findRecommendedIdByName(
        array $values,
        string $name
    ): ?int {
        $result = null;
        $attributeName = $this->normalizeAttributeValue($name);
        foreach ($values as $attributeValue) {
            $attributeValueName = $this->normalizeAttributeValue($attributeValue->getName());

            if ($attributeName === $attributeValueName) {
                $result = (int)$attributeValue->getId();
                break;
            }
        }

        return $result;
    }

    private function normalizeAttributeValue(string $value): string
    {
        $removePunctuation = str_replace([' ', '_', '-', '.'], '', $value);

        return strtolower($removePunctuation);
    }

    private function processFail(
        \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $dictionaryAttribute,
        string $attributeName
    ): Result {
        $message = $dictionaryAttribute->isRequired()
            ? $this->compileErrorMessage($attributeName)
            : $this->compileWarningMessage($attributeName);

        return Result::createFail($message);
    }

    private function compileWarningMessage(string $attributeName): string
    {
        return (string)__(
            'The value set for the attribute: %1 does not match any of the supported options'
            . ' and was not synchronized to the channel.',
            $attributeName
        );
    }

    private function compileErrorMessage(string $attributeName): string
    {
        return (string)__(
            'Invalid value set for attribute: %1. The provided value does not match any of the '
            . 'supported options.',
            $attributeName
        );
    }
}
