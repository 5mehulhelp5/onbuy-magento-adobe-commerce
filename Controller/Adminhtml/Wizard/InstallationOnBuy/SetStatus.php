<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

use M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class SetStatus extends Installation
{
    public function execute()
    {
        return $this->setStatusAction();
    }
}
