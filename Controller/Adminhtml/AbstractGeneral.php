<?php

namespace M2E\OnBuy\Controller\Adminhtml;

abstract class AbstractGeneral extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::onbuy');
    }
}
