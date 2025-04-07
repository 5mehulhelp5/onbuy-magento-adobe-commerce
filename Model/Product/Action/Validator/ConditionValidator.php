<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class ConditionValidator implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if (empty($product->getListing()->getCondition())) {
            return (string)__(
                'The Product Сondition is not specified. Please select the correct ' .
                'Сondition in the Listing settings before listing it on the Channel.',
            );
        }

        return null;
    }
}
