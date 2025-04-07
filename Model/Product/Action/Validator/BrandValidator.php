<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class BrandValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        $resolveResult = $product->getDataProvider()->getProductBrand();
        if ($resolveResult->isSuccess()) {
            return null;
        }

        $error = (string)__('Brand is not valid');
        $errors = $resolveResult->getMessages();
        if (!empty($errors)) {
            $error = reset($errors);
        }

        return $error;
    }
}
