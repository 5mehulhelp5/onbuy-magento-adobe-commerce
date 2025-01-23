<?php

namespace M2E\OnBuy\Controller\Adminhtml\Synchronization;

abstract class AbstractLog extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    protected function getMenuRootNodeNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::MENU_ROOT_NODE_NICK;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::help_center_synchronization_log');
    }
}
