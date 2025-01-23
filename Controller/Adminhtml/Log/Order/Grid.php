<?php

namespace M2E\OnBuy\Controller\Adminhtml\Log\Order;

class Grid extends \M2E\OnBuy\Controller\Adminhtml\Log\AbstractOrder
{
    public function execute()
    {
        $response = $this->getLayout()
                         ->createBlock(\M2E\OnBuy\Block\Adminhtml\Log\Order\Grid::class)
                         ->toHtml();
        $this->setAjaxContent($response);

        return $this->getResult();
    }
}
