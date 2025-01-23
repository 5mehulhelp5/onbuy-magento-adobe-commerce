<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Product\Rule\Custom\OnBuy;

class OnlineQty extends \M2E\OnBuy\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'online_qty';

    public function getInputType(): string
    {
        return \M2E\OnBuy\Model\Magento\Product\Rule\Condition\AbstractModel::INPUT_TYPE_NUMERIC;
    }

    public function getLabel(): string
    {
        return (string)__('Available QTY');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('online_qty');
    }
}
