<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action\Validator;

interface ValidatorInterface
{
    public function validate(\M2E\OnBuy\Model\Product $product): ?string;
}
