<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Category\Dictionary;

class AttributeFactory
{
    /**
     * @param \M2E\OnBuy\Model\Category\Dictionary\Attribute\Value[] $values
     */
    public function createProductAttribute(
        string $id,
        string $name,
        bool $isRequired,
        array $values
    ): \M2E\OnBuy\Model\Category\Dictionary\Attribute\ProductAttribute {
        return new \M2E\OnBuy\Model\Category\Dictionary\Attribute\ProductAttribute(
            $id,
            $name,
            $isRequired,
            $values
        );
    }

    public function createValue(
        string $id,
        string $name
    ): \M2E\OnBuy\Model\Category\Dictionary\Attribute\Value {
        return new \M2E\OnBuy\Model\Category\Dictionary\Attribute\Value($id, $name);
    }
}
