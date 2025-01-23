<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel;

use M2E\OnBuy\Helper\Module;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \M2E\OnBuy\Controller\Adminhtml\ControlPanel\OverviewTab
 */
class OverviewTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Overview::class, '');
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
