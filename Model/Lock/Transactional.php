<?php

namespace M2E\OnBuy\Model\Lock;

/**
 * Class \M2E\OnBuy\Model\Lock\Transactional
 */
class Transactional extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\Lock\Transactional::class);
    }

    public function getNick()
    {
        return $this->getData('nick');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }
}
