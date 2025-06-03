<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class BrandValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product
    ): ?ValidatorMessage {
        $resolveResult = $product->getDataProvider()->getProductBrand();
        if ($resolveResult->isSuccess()) {
            return null;
        }

        $error = (string)__('Brand is not valid');
        $errors = $resolveResult->getMessages();
        if (!empty($errors)) {
            $error = reset($errors);
        }

        return new ValidatorMessage(
            $error,
            \M2E\OnBuy\Model\Tag\ValidatorIssues::ERROR_BRAND_INVALID_OR_MISSING
        );
    }
}
