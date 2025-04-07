<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Category;

class SaveCategoryAttributesAjax extends \M2E\OnBuy\Controller\Adminhtml\AbstractCategory
{
    private \M2E\OnBuy\Model\Category\CategoryAttributeFactory $attributeFactory;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\OnBuy\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\OnBuy\Model\Category\CategoryAttributeFactory $attributeFactory,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\OnBuy\Model\Category\Attribute\Manager $attributeManager
    ) {
        parent::__construct();

        $this->attributeFactory = $attributeFactory;
        $this->dictionaryRepository = $dictionaryRepository;
        $this->attributeManager = $attributeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost()->toArray();

        if (
            empty($post['dictionary_id'])
            || empty($post['attributes'])
        ) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Invalid input');
        }

        try {
            $attributes = json_decode($post['attributes'], true);
            $dictionary = $this->dictionaryRepository->get((int)$post['dictionary_id']);

            $allAttributes = array_merge(
                array_values($attributes['real_attributes'] ?? []),
                array_values($attributes['virtual_attributes'] ?? [])
            );

            $allAttributes = $this->getAttributes($dictionary->getId(), $allAttributes);

            $this->attributeManager->createOrUpdateAttributes($allAttributes, $dictionary);
        } catch (\M2E\OnBuy\Model\Exception\Logic $e) {
            $this->setJsonContent(
                [
                    'success' => false,
                    'messages' => [
                        ['error' => (string)__('Attributes not saved')],
                    ],
                ]
            );
        }

        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }

    /**
     * @param int $dictionaryId
     * @param array $inputAttributes
     *
     * @return \M2E\OnBuy\Model\Category\CategoryAttribute[]
     */
    private function getAttributes(int $dictionaryId, array $inputAttributes): array
    {
        $attributes = [];
        foreach ($inputAttributes as $inputAttribute) {
            $recommendedValues = [];
            if (!empty($inputAttribute['value_onbuy_recommended'])) {
                $recommendedValues = $this->getRecommendedValues($inputAttribute['value_onbuy_recommended']);
            }
            $attributes[] = $this->attributeFactory->create()->create(
                $dictionaryId,
                $inputAttribute['attribute_type'],
                $inputAttribute['attribute_id'],
                $inputAttribute['attribute_name'],
                (int)$inputAttribute['value_mode'],
                $recommendedValues,
                $inputAttribute['value_custom_value'] ?? '',
                $inputAttribute['value_custom_attribute'] ?? ''
            );
        }

        return $attributes;
    }

    /**
     * @param array|string $inputValues
     *
     * @return string[]
     */
    private function getRecommendedValues($inputValues): array
    {
        if (is_string($inputValues)) {
            $inputValues = [$inputValues];
        }

        $values = [];
        foreach ($inputValues as $value) {
            if (!empty($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
