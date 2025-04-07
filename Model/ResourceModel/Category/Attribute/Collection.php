<?php

namespace M2E\OnBuy\Model\ResourceModel\Category\Attribute;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Category\CategoryAttribute::class,
            \M2E\OnBuy\Model\ResourceModel\Category\Attribute::class
        );
    }
}
