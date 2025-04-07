<?php

namespace M2E\OnBuy\Model\ResourceModel\Category\Dictionary;

/**
 * @method \M2E\OnBuy\Model\Category\Dictionary getFirstItem()
 * @method \M2E\OnBuy\Model\Category\Dictionary[] getItems()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Category\Dictionary::class,
            \M2E\OnBuy\Model\ResourceModel\Category\Dictionary::class
        );
    }
}
