<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class DescriptionValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        $description = $product->getDataProvider()->getDescription()->getValue()->description;

        if (empty($description)) {
            return new ValidatorMessage(
                (string)__('Product Description is missing'),
                \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_DESCRIPTION_MISSING
            );
        }

        return null;
    }
}
