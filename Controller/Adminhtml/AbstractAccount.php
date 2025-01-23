<?php

namespace M2E\OnBuy\Controller\Adminhtml;

use M2E\OnBuy\Controller\Adminhtml\AbstractMain;

abstract class AbstractAccount extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::configuration_accounts');
    }
}
