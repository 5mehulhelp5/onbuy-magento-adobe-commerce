<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\InventorySync\ReceivedProduct::class,
            \M2E\OnBuy\Model\ResourceModel\InventorySync\ReceivedProduct::class
        );
    }
}
