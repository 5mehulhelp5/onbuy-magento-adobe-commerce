<?php

namespace M2E\OnBuy\Model\ResourceModel\Processing\PartialData;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Processing\PartialData::class,
            \M2E\OnBuy\Model\ResourceModel\Processing\PartialData::class
        );
    }
}
