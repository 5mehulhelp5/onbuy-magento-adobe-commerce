<?php

namespace M2E\OnBuy\Model\Magento\Product;

use M2E\OnBuy\Model\Magento\Product\Inventory\AbstractModel;

class Inventory extends AbstractModel
{
    /**
     * @return bool|int|mixed
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function isInStock()
    {
        return $this->getStockItem()->getIsInStock();
    }

    /**
     * @return float|mixed
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function getQty()
    {
        return $this->getStockItem()->getQty();
    }
}
