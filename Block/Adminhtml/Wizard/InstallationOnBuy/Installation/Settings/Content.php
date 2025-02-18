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
            __(
                'In this section, you can configure the general settings for the interaction ' .
                'between %extension_title and %channel_title.<br><br>Anytime you can change these ' .
                'settings under <b>%channel_title > Configuration > General</b>.',
                [
                    'extension_title' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            )
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
