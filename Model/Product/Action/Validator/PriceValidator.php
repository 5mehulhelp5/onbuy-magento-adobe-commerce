<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class PriceValidator implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        if ($product->getDataProvider()->getPrice()->getValue()->price === 0.0) {
            return new ValidatorMessage(
                (string)__('The Product Price must be greater than 0.'),
                \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_ZERO_PRICE
            );
        }

        return null;
    }
}
