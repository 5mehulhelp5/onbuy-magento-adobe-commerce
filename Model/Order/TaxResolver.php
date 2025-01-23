<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Order;

class TaxResolver
{
    /**
     * @return int|float
     */
    public static function calculateRate(float $totalPrice, float $taxTotal)
    {
        $priceWithoutTax = $totalPrice - $taxTotal;
        $rate = ($taxTotal * 100) / $priceWithoutTax;

        $decimalPart = $rate - floor($rate);

        if ($decimalPart === 0.5) {
            $rate = round($rate, 2);
        } else {
            $rate = round($rate);
        }

        return $rate;
    }
}
