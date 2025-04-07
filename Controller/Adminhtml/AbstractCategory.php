<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml;

abstract class AbstractCategory extends \M2E\OnBuy\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::listings_items');
    }
}
