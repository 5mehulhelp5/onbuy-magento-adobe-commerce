<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain;
use M2E\OnBuy\Helper\Module;
use Magento\Backend\App\Action;

/**
 * Class \M2E\OnBuy\Controller\Adminhtml\ControlPanel\Inspection\PhpInfo
 */
class PhpInfo extends AbstractMain
{
    public function execute()
    {
        phpinfo();
    }
}
