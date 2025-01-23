<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Rule\Custom\OnBuy;

class OnlineCategory extends \M2E\OnBuy\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'onbuy_online_category';

    public function getLabel(): string
    {
        return (string)__('Category ID');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('online_category');
    }
}
