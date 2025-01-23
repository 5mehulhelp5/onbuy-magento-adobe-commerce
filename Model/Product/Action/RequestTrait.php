<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\Action;

trait RequestTrait
{
    private function processDataProviderLogs(\M2E\OnBuy\Model\Product\DataProvider $dataProvider): void
    {
        foreach ($dataProvider->getLogs() as $log) {
            $this->addWarningMessage($log);
        }
    }
}
