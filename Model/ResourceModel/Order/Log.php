<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Order;

class Log extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(\M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_ORDER_LOG, 'id');
    }
}
