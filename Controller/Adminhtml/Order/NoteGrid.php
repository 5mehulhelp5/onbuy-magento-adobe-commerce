<?php

namespace M2E\OnBuy\Controller\Adminhtml\Order;

use M2E\OnBuy\Controller\Adminhtml\AbstractOrder;

class NoteGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Note\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
