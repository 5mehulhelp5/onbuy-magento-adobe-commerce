<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class IdentifierValidator implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if (empty($product->getDataProvider()->getIdentifier()->getValue())) {
            return (string)__(
                'EAN is missing a value'
            );
        }

        return null;
    }
}
