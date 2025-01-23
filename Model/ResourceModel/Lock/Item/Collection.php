<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Lock\Item;

/**
 * @method \M2E\OnBuy\Model\Lock\Item getFirstItem()
 * @method \M2E\OnBuy\Model\Lock\Item[] getItems()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Model\Lock\Item::class,
            \M2E\OnBuy\Model\ResourceModel\Lock\Item::class
        );
    }
}
