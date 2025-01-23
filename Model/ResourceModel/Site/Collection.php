<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Site;

/**
 * @method \M2E\OnBuy\Model\Site[] getItems()
 * @method \M2E\OnBuy\Model\Site getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Site::class,
            \M2E\OnBuy\Model\ResourceModel\Site::class
        );
    }
}
