<?php

namespace M2E\OnBuy\Controller\Adminhtml\OnBuy;

abstract class AbstractMain extends \M2E\OnBuy\Controller\Adminhtml\AbstractMain
{
    private bool $isInitResultPage = false;

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::onbuy');
    }

    protected function getCustomViewNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::NICK;
    }

    protected function initResultPage(): void
    {
        if ($this->isInitResultPage) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(\M2E\OnBuy\Helper\View\OnBuy::getTitle());

        if ($this->getLayoutType() !== self::LAYOUT_BLANK) {
            /** @psalm-suppress UndefinedMethod */
            $this->getResultPage()->setActiveMenu(\M2E\OnBuy\Helper\View\OnBuy::MENU_ROOT_NODE_NICK);
        }

        $this->isInitResultPage = true;
    }
}
