<?php

namespace M2E\OnBuy\Block\Adminhtml\Wizard\InstallationOnBuy\Installation\Account;

use M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm;

class Content extends AbstractForm
{
    private \M2E\OnBuy\Block\Adminhtml\Account\CredentialsFormFactory $credentialsFormFactory;

    public function __construct(
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\OnBuy\Block\Adminhtml\Account\CredentialsFormFactory $credentialsFormFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->credentialsFormFactory = $credentialsFormFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('wizardInstallationWizardTutorial');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            (string)__('On this step, you should link your OnBuy Account with your M2E OnBuy Connect.<br/><br/>')
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = $this->credentialsFormFactory->create(true, true, false, 'edit_form');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsTranslator->add(
            'An error during of account creation.',
            __('The OnBuy token obtaining is currently unavailable. Please try again later.')
        );

        return parent::_beforeToHtml();
    }
}
