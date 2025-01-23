<?php

namespace M2E\OnBuy\Controller\Adminhtml;

abstract class AbstractSettings extends \M2E\OnBuy\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::configuration_settings');
    }
}
