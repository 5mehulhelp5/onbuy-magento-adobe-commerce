<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

use M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class Congratulation extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->congratulationAction();
    }
}
