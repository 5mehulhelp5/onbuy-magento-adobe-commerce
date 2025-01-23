<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class Installation extends \M2E\OnBuy\Controller\Adminhtml\Wizard\AbstractInstallation
{
    public function execute()
    {
        return $this->installationAction();
    }
}
