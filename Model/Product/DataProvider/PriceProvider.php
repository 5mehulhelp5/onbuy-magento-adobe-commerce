<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Product\DataProvider;

class PriceProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Price';
    private \M2E\OnBuy\Model\Product\PriceCalculatorFactory $priceCalculatorFactory;

    public function __construct(\M2E\OnBuy\Model\Product\PriceCalculatorFactory $priceCalculatorFactory)
    {
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function getPrice(\M2E\OnBuy\Model\Product $product): \M2E\OnBuy\Model\Product\DataProvider\Price\Value
    {
        $price = $this->getCalculatedPriceWithModifier($product);

        return new \M2E\OnBuy\Model\Product\DataProvider\Price\Value(
            $price,
            $product->getCurrencyCode()
        );
    }

    private function getCalculatedPriceWithModifier(
        \M2E\OnBuy\Model\Product $product
    ): float {
        $src = $product->getSellingFormatTemplate()->getFixedPriceSource();
        $priceModifier = $product->getSellingFormatTemplate()->getFixedPriceModifier();

        $calculator = $this->priceCalculatorFactory->create($product);
        $calculator->setSource($src);
        $calculator->setModifier($priceModifier);

        return (float)$calculator->getProductValue();
    }
}
