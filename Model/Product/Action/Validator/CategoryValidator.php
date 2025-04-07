<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class CategoryValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if (!$configurator->isCategoriesAllowed()) {
            return null;
        }

        if (!$product->hasCategoryTemplate()) {
            return 'Categories Settings are not set';
        }

        return null;
    }
}
