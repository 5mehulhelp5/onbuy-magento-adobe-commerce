<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel;

use M2E\OnBuy\Helper\Module;
use Magento\Backend\App\Action;

/**
 * Class \M2E\OnBuy\Controller\Adminhtml\ControlPanel\InspectionTab
 */
class InspectionTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\ControlPanel\Tabs\Inspection::class,
            ''
        );
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
