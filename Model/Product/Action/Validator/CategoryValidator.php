<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class CategoryValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        if (!$product->hasCategoryTemplate()) {
            return new ValidatorMessage(
                (string)__('Categories Settings are not set'),
                \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_CATEGORY_SETTINGS_NOT_SET
            );
        }

        return null;
    }
}
