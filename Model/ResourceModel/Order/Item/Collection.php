<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Order\Item;

/**
 * @method \M2E\OnBuy\Model\Order\Item[] getItems()
 * @method \M2E\OnBuy\Model\Order\Item getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Order\Item::class,
            \M2E\OnBuy\Model\ResourceModel\Order\Item::class
        );
    }
}
