<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

class TitleValidator implements ValidatorInterface
{
    public function validate(
        \M2E\OnBuy\Model\Product $product,
        \M2E\OnBuy\Model\Product\Action\Configurator $configurator
    ): ?string {
        if (!$configurator->isTitleAllowed()) {
            return null;
        }

        $title = $product->getDataProvider()->getTitle()->getValue();

        $titleLength = mb_strlen($title);

        if ($titleLength < 1 || $titleLength > 150) {
            return 'The product name must contain between 1 and 150 characters.';
        }

        return null;
    }
}
