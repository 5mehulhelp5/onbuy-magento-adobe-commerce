<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\Stop;

class Validator extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidator
{
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isStoppable()) {
            $this->addMessage(
                new \M2E\OnBuy\Model\Product\Action\Validator\ValidatorMessage(
                    'Item is not Listed or not available',
                    \M2E\OnBuy\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        return true;
    }
}
