<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class TitleValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        $title = $product->getDataProvider()->getTitle()->getValue();

        $titleLength = mb_strlen($title);

        if ($titleLength < 1 || $titleLength > 150) {
            return new ValidatorMessage(
                (string)__('The product name must contain between 1 and 150 characters.'),
                \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_INVALID_PRODUCT_NAME_LENGTH
            );
        }

        return null;
    }
}
