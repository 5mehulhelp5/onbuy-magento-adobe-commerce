<?php

namespace M2E\OnBuy\Controller\Adminhtml\Synchronization\Log;

class Grid extends \M2E\OnBuy\Controller\Adminhtml\Synchronization\AbstractLog
{
    public function execute()
    {
        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Synchronization\Log\Grid::class)
        );

        return $this->getResult();
    }
}
