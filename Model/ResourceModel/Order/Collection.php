<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Order;

/**
 * @method \M2E\OnBuy\Model\Order[] getItems()
 * @method \M2E\OnBuy\Model\Order getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Order::class,
            \M2E\OnBuy\Model\ResourceModel\Order::class
        );
    }
}
