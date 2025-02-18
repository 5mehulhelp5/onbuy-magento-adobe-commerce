<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Type\ListAction;

class ValidatorFactory extends \M2E\OnBuy\Model\Product\Action\Type\AbstractValidatorFactory
{
    protected function getValidatorClass(): string
    {
        return Validator::class;
    }
}
