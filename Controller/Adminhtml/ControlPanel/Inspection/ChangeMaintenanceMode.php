<?php

namespace M2E\OnBuy\Controller\Adminhtml\ControlPanel\Inspection;

class ChangeMaintenanceMode extends \M2E\OnBuy\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper;
    private \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper;

    public function __construct(
        \M2E\OnBuy\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\OnBuy\Helper\Module\Maintenance $maintenanceHelper
    ) {
        parent::__construct();
        $this->controlPanelHelper = $controlPanelHelper;
        $this->maintenanceHelper = $maintenanceHelper;
    }

    public function execute()
    {
        if ($this->maintenanceHelper->isEnabled()) {
            $this->maintenanceHelper->disable();
        } else {
            $this->maintenanceHelper->enable();
        }

        $this->messageManager->addSuccess(__('Changed.'));

        return $this->_redirect($this->controlPanelHelper->getPageUrl());
    }
}
