<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Template\Category;

use M2E\OnBuy\Model\Category\CategoryAttribute;

class DictionaryMapper
{
    private \M2E\OnBuy\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\OnBuy\Model\AttributeMapping\GeneralService $generalService;
    /** @var \M2E\Core\Model\AttributeMapping\Pair[] */
    private array $generalAttributeMapping;

    public function __construct(
        \M2E\OnBuy\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\OnBuy\Model\AttributeMapping\GeneralService $generalService
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->generalService = $generalService;
    }

    /**
     * @see \M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Specific\Form\Element\Dictionary
     */
    public function getProductAttributes(\M2E\OnBuy\Model\Category\Dictionary $dictionary): array
    {
        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();
        $savedAttributes = $this->loadSavedAttributes($dictionary, [
            CategoryAttribute::ATTRIBUTE_TYPE_PRODUCT,
        ]);

        $attributes = [];
        foreach ($dictionary->getProductAttributes() as $productAttribute) {
            $item = $this->map($productAttribute, $savedAttributes, $generalMappingAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    public function getVirtualAttributes(\M2E\OnBuy\Model\Category\Dictionary $dictionary): array
    {
        $generalMappingAttributes = $this->getGeneralAttributesMappingByAttributeId();
        $savedAttributes = $this->loadSavedAttributes($dictionary, [CategoryAttribute::ATTRIBUTE_TYPE_BRAND]);

        $attributes = [];
        foreach ($dictionary->getBrandAttribute() as $virtualAttribute) {
            $item = $this->map($virtualAttribute, $savedAttributes, $generalMappingAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    /**
     * @param \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $attribute
     * @param \M2E\OnBuy\Model\Category\CategoryAttribute[] $savedAttributes
     * @param \M2E\Core\Model\AttributeMapping\Pair[] $generalMappingAttributes
     *
     * @return array
     */
    private function map(
        \M2E\OnBuy\Model\Category\Dictionary\AbstractAttribute $attribute,
        array $savedAttributes,
        array $generalMappingAttributes = []
    ): array {
        $item = [
            'id' => $attribute->getId(),
            'title' => $attribute->getName(),
            'attribute_type' => $attribute->getType(),
            'type' => $attribute->isMultipleSelected() ? 'select_multiple' : 'select',
            'required' => $attribute->isRequired(),
            'min_values' => $attribute->isRequired() ? 1 : 0,
            'max_values' => $attribute->isMultipleSelected() ? count($attribute->getValues()) : 1,
            'values' => [],
            'template_attribute' => []
        ];

        $existsAttribute = $savedAttributes[$attribute->getId()] ?? null;
        $generalMapping = $generalMappingAttributes[$attribute->getId()] ?? null;
        if (
            $existsAttribute !== null
            || $generalMapping !== null
        ) {
            $item['template_attribute'] = [
                'id' => $existsAttribute ? $existsAttribute->getAttributeId() : null,
                'template_category_id' => $existsAttribute ? $existsAttribute->getId() : null,
                'mode' => '1',
                'attribute_title' => $existsAttribute ? $existsAttribute->getAttributeId() : $attribute->getName(),
                'value_mode' => $existsAttribute !== null
                    ? $existsAttribute->getValueMode()
                    : ($generalMapping !== null ? \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE : \M2E\OnBuy\Model\Category\CategoryAttribute::VALUE_MODE_NONE),
                'value_onbuy_recommended' => $existsAttribute ? $existsAttribute->getRecommendedValue() : null,
                'value_custom_value' => $existsAttribute ? $existsAttribute->getCustomValue() : null,
                'value_custom_attribute' => $existsAttribute !== null
                    ? $existsAttribute->getCustomAttributeValue()
                    : ($generalMapping !== null ? $generalMapping->getMagentoAttributeCode() : null),
            ];
        }

        foreach ($attribute->getValues() as $value) {
            $item['values'][] = [
                'id' => $value->getId(),
                'value' => $value->getName(),
            ];
        }

        return $item;
    }

    private function loadSavedAttributes(
        \M2E\OnBuy\Model\Category\Dictionary $dictionary,
        array $typeFilter = []
    ): array {
        $attributes = [];

        $savedAttributes = $this
            ->attributeRepository
            ->findByDictionaryId($dictionary->getId(), $typeFilter);

        foreach ($savedAttributes as $attribute) {
            $attributes[$attribute->getAttributeId()] = $attribute;
        }

        return $attributes;
    }

    public function sortAttributesByTitle(array $attributes): array
    {
        usort($attributes, function ($prev, $next) {
            return strcmp($prev['title'], $next['title']);
        });

        $requiredAttributes = [];
        foreach ($attributes as $index => $attribute) {
            if (isset($attribute['required']) && $attribute['required'] === true) {
                $requiredAttributes[] = $attribute;
                unset($attributes[$index]);
            }
        }

        return array_merge($requiredAttributes, $attributes);
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getGeneralAttributesMappingByAttributeId(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->generalAttributeMapping)) {
            return $this->generalAttributeMapping;
        }

        $result = [];
        foreach ($this->generalService->getAll() as $item) {
            $result[$item->getChannelAttributeCode()] = $item;
        }

        return $this->generalAttributeMapping = $result;
    }
}
