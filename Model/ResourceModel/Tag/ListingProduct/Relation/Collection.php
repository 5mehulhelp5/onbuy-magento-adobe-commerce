<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Tag\ListingProduct\Relation;

use M2E\OnBuy\Model\ResourceModel\Tag\ListingProduct\Relation as ResourceModel;
use M2E\OnBuy\Model\Tag\ListingProduct\Relation;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(Relation::class, ResourceModel::class);
    }
}
