<?php

namespace M2E\OnBuy\Controller\Adminhtml\Log;

abstract class AbstractListing extends \M2E\OnBuy\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::listings_logs');
    }
}
