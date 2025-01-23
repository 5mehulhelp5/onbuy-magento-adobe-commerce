<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

interface DataBuilderInterface
{
    public function getWarningMessages(): array;
}
