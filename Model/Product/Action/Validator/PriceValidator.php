<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class PriceValidator implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if ($product->getDataProvider()->getPrice()->getValue()->price === 0.0) {
            return (string)__(
                'The Product Price must be greater than 0.',
            );
        }

        return null;
    }
}
