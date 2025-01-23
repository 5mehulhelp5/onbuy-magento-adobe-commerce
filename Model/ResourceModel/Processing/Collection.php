<?php

namespace M2E\OnBuy\Model\ResourceModel\Processing;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \M2E\OnBuy\Model\Processing::class,
            \M2E\OnBuy\Model\ResourceModel\Processing::class
        );
    }
}
