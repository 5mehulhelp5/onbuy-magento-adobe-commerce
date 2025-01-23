<?php

namespace M2E\OnBuy\Model\ResourceModel\Lock\Transactional;

/**
 * Class \M2E\OnBuy\Model\ResourceModel\Lock\Transactional\Collection
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\OnBuy\Model\Lock\Transactional::class,
            \M2E\OnBuy\Model\ResourceModel\Lock\Transactional::class
        );
    }

    //########################################
}
