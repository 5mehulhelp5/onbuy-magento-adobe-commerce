<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

use M2E\OnBuy\Controller\Adminhtml\AbstractOrder;

class ProductMappingGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
