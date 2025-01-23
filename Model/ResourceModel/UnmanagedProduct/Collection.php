<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\UnmanagedProduct;

/**
 * @method \M2E\OnBuy\Model\UnmanagedProduct[] getItems()
 * @method \M2E\OnBuy\Model\UnmanagedProduct[] getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\UnmanagedProduct::class,
            \M2E\OnBuy\Model\ResourceModel\UnmanagedProduct::class
        );
    }
}
