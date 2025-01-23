<?php

namespace M2E\OnBuy\Block\Adminhtml\Wizard\InstallationOnBuy;

abstract class Installation extends \M2E\OnBuy\Block\Adminhtml\Wizard\Installation
{
    protected function _construct()
    {
        parent::_construct();

        $this->updateButton('continue', 'onclick', 'InstallationWizardObj.continueStep();');
    }

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'OnBuy/Wizard/InstallationOnBuy',
    ], function(){
        window.InstallationWizardObj = new WizardInstallationOnBuy();
    });
JS
        );

        return parent::_toHtml();
    }
}
