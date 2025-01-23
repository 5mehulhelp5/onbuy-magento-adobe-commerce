<?php

namespace M2E\OnBuy\Model\ResourceModel\OperationHistory;

/**
 * Class \M2E\OnBuy\Model\ResourceModel\OperationHistory\Collection
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\OnBuy\Model\OperationHistory::class,
            \M2E\OnBuy\Model\ResourceModel\OperationHistory::class
        );
    }

    //########################################
}
