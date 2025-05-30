<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Inspection;

class SetMagentoCoreSetupValue extends \M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \Magento\Framework\Module\ModuleResource $moduleResource;
    private \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper;
    private \M2E\OnBuy\Setup\UpgradeCollection $updateCollection;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $dbContext,
        \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\OnBuy\Setup\UpgradeCollection $updateCollection
    ) {
        parent::__construct();
        $this->moduleResource = new \Magento\Framework\Module\ModuleResource($dbContext);
        $this->controlPanelHelper = $controlPanelHelper;
        $this->updateCollection = $updateCollection;
    }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        if (!$version) {
            $this->messageManager->addWarning('Version is not provided.');

            return $this->_redirect($this->controlPanelHelper->getPageUrl());
        }

        $version = str_replace(',', '.', $version);
        if (!version_compare($this->updateCollection->getMinAllowedVersion(), $version, '<=')) {
            $this->messageManager->addError(
                sprintf(
                    'Extension upgrade can work only from %s version.',
                    $this->updateCollection->getMinAllowedVersion()
                )
            );

            return $this->_redirect($this->controlPanelHelper->getPageUrl());
        }

        $this->moduleResource->setDbVersion(\M2E\OnBuy\Helper\Module::IDENTIFIER, $version);
        $this->moduleResource->setDataVersion(\M2E\OnBuy\Helper\Module::IDENTIFIER, $version);

        $this->messageManager->addSuccess(__('Extension upgrade was completed.'));

        return $this->_redirect($this->controlPanelHelper->getPageUrl());
    }
}
