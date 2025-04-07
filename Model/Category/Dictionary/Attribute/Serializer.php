<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary\Attribute;

class Serializer
{
    private \M2E\OnBuy\Model\Category\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\OnBuy\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param ProductAttribute[] $attributes
     *
     * @return string
     */
    public function serializeProductAttributes(array $attributes): string
    {
        $data = [];
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof ProductAttribute) {
                throw new \LogicException('Invalid attribute instance');
            }

            $values = [];
            foreach ($attribute->getValues() as $value) {
                $values[] = [
                    'id' => $value->getId(),
                    'name' => $value->getName()
                ];
            }

            $data[] = [
                'id' => $attribute->getId(),
                'name' => $attribute->getName(),
                'is_required' => $attribute->isRequired(),
                'values' => $values
            ];
        }

        return json_encode($data);
    }

    /**
     * @return ProductAttribute[]
     */
    public function unSerializeProductAttributes(string $jsonAttributes): array
    {
        $attributes = [];
        foreach (json_decode($jsonAttributes, true) as $item) {
            $values = [];
            foreach ($item['values'] as $value) {
                $values[] = $this->attributeFactory->createValue(
                    $value['id'],
                    $value['name']
                );
            }

            $attributes[] = $this->attributeFactory->createProductAttribute(
                (string)$item['id'],
                $item['name'],
                $item['is_required'],
                $values
            );
        }

        return $attributes;
    }
}
