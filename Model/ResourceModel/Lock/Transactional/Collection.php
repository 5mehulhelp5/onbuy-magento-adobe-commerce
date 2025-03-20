<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Lock\Transactional;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Model\Lock\Transactional::class,
            \M2E\OnBuy\Model\ResourceModel\Lock\Transactional::class
        );
    }
}
