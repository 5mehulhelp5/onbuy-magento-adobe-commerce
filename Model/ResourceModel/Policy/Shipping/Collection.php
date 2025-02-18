<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Policy\Shipping;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Policy\Shipping::class,
            \M2E\OnBuy\Model\ResourceModel\Policy\Shipping::class
        );
    }
}
