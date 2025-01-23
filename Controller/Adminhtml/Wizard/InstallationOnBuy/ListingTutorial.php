<?php

namespace M2E\OnBuy\Controller\Adminhtml\Wizard\InstallationOnBuy;

class ListingTutorial extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
