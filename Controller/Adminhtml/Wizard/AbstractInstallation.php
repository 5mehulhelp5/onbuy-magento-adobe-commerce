<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard;

abstract class AbstractInstallation extends \M2E\OnBuy\Controller\Adminhtml\AbstractWizard
{
    protected function getNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::WIZARD_INSTALLATION_NICK;
    }

    protected function init(): void
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Configuration of %channel Integration', ['channel' => (string)__('OnBuy')]));
    }

    protected function getCustomViewNick(): string
    {
        return \M2E\OnBuy\Helper\View\OnBuy::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return \M2E\OnBuy\Helper\View\OnBuy::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return \M2E\OnBuy\Helper\View\OnBuy::getMenuRootNodeLabel();
    }
}
