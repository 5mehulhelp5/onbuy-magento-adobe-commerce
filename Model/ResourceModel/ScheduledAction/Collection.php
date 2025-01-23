<?php

namespace M2E\OnBuy\Model\ResourceModel\ScheduledAction;

use M2E\OnBuy\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\OnBuy\Model\ScheduledAction::class,
            \M2E\OnBuy\Model\ResourceModel\ScheduledAction::class
        );
    }

    // ----------------------------------------

    public function addTagFilter(string $tag, bool $canBeEmpty = false): self
    {
        $whereExpression = sprintf("main_table.%s LIKE '%%$tag%%'", ScheduledActionResource::COLUMN_TAG);
        if ($canBeEmpty) {
            $whereExpression .= sprintf(
                " OR main_table.%s IS NULL OR main_table.%s = ''",
                ScheduledActionResource::COLUMN_TAG,
                ScheduledActionResource::COLUMN_TAG,
            );
        }

        $this->getSelect()->where($whereExpression);

        return $this;
    }
}
