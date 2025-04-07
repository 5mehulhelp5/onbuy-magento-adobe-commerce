<?php

namespace M2E\OnBuy\Model\ResourceModel\Category;

class Tree extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const ID_FIELD = 'id';
    public const COLUMN_SITE_ID = 'site_id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_PARENT_CATEGORY_ID = 'parent_category_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_IS_LEAF = 'is_leaf';
    public const COLUMN_PERMISSION_STATUSES = 'permission_statuses';

    protected function _construct()
    {
        $this->_init(\M2E\OnBuy\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY_TREE, self::ID_FIELD);
    }
}
