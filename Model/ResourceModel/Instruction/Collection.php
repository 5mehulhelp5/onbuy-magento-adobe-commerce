<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\ResourceModel\Instruction;

/**
 * @method \M2E\OnBuy\Model\Instruction[] getItems()
 */
class Collection extends \M2E\OnBuy\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\OnBuy\Model\Instruction::class,
            \M2E\OnBuy\Model\ResourceModel\Instruction::class
        );
    }
}
