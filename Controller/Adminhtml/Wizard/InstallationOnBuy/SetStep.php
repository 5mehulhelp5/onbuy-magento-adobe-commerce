<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

use M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class SetStep extends Installation
{
    public function execute()
    {
        return $this->setStepAction();
    }
}
