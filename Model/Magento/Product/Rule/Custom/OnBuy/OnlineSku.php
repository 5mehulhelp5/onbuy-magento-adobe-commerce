<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Rule\Custom\OnBuy;

class OnlineSku extends \M2E\OnBuy\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'online_sku';

    public function getLabel(): string
    {
        return (string)__('SKU');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('online_sku');
    }
}
