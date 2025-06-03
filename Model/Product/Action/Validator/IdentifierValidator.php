<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class IdentifierValidator implements \M2E\OnBuy\Model\Product\Action\Validator\ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        if (empty($product->getDataProvider()->getIdentifier()->getValue())) {
            return new ValidatorMessage(
                (string)__('EAN is missing a value'),
                \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_EAN_MISSING
            );
        }

        return null;
    }
}
