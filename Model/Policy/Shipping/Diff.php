<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy\Shipping;

class Diff extends \M2E\OnBuy\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isShippingDataDifferent();
    }

    public function isShippingDataDifferent(): bool
    {
        $keys = [
            \M2E\OnBuy\Model\ResourceModel\Policy\Shipping::COLUMN_DELIVERY_TEMPLATE_ID,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
