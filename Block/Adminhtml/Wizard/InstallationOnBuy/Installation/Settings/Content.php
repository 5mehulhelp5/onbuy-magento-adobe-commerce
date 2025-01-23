<?php

namespace M2E\OnBuy\Block\Adminhtml\Wizard\InstallationOnBuy\Installation\Settings;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm;

class Content extends AbstractForm
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationSettings');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            __('In this section, you can configure the general settings for the interaction ' .
                'between M2E OnBuy Connect and OnBuy.<br><br>Anytime you can change these ' .
                'settings under <b>OnBuy > Configuration > General</b>.')
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $settings = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Settings\Tabs\Main::class);

        $settings->toHtml();
        $form = $settings->getForm();

        $form->setData([
            'id' => 'edit_form',
            'method' => 'post',
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);
    }
}
