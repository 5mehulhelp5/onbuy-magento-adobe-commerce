<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Rule\Custom\OnBuy;

class ProductId extends \M2E\OnBuy\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'channel_product_id';

    public function getLabel(): string
    {
        return (string)__('Product Id');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('product_id');
    }
}
