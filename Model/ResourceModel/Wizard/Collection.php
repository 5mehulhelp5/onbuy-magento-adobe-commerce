<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Wizard;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Model\Wizard::class,
            \M2E\OnBuy\Model\ResourceModel\Wizard::class
        );
    }
}
