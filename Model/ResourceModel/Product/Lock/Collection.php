<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Product\Lock;

/**
 * @method \M2E\OnBuy\Model\Product getFirstItem()
 * @method \M2E\OnBuy\Model\Product[] getItems()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Product\Lock::class,
            \M2E\OnBuy\Model\ResourceModel\Product\Lock::class
        );
    }
}
