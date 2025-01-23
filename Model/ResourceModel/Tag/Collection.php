<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Tag;

/**
 * @method \M2E\OnBuy\Model\Tag\Entity[] getItems()
 * @method \M2E\OnBuy\Model\Tag\Entity[] getFirstItem()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\Tag\Entity::class,
            \M2E\OnBuy\Model\ResourceModel\Tag::class
        );
    }
}
