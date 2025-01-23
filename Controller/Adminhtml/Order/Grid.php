<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

class Grid extends AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\OnBuy\Block\Adminhtml\Order\Grid $grid */
        $grid = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
