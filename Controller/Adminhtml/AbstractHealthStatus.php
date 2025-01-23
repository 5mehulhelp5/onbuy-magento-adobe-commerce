<?php

namespace M2E\OnBuy\Controller\Adminhtml;

abstract class AbstractHealthStatus extends \M2E\OnBuy\Controller\Adminhtml\AbstractBase
{
    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::help_center_health_status');
    }

    protected function getMenuRootNodeNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::MENU_ROOT_NODE_NICK;
    }
}
