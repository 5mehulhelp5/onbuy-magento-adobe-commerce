<?php

namespace M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs;

class General extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    private ?\M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\OnBuy\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        $this->accountUrlHelper = $accountUrlHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    // ----------------------------------------

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $content = __(
            'This Page shows the Environment for your OnBuy Account and details of the ' .
            'authorisation for M2E OnBuy Connect to connect to your OnBuy Account.<br/><br/> If your token has ' .
            'expired or is not activated, click <b>Get Token</b>.<br/><br/>'
        );

        $form->addField(
            'onbuy_accounts_general',
            self::HELP_BLOCK,
            [
                'content' => $content,
            ],
        );

        if ($this->account !== null) {
            $fieldset = $form->addFieldset(
                'general',
                [
                    'legend' => __('General'),
                    'collapsable' => false,
                ],
            );

            $fieldset->addField(
                'title',
                'text',
                [
                    'name' => 'title',
                    'class' => 'OnBuy-account-title',
                    'label' => __('Title'),
                    'value' => $this->account->getTitle(),
                    'tooltip' => __('Title or Identifier of OnBuy Account for your internal use.'),
                ],
            );
        }

        $fieldset = $form->addFieldset(
            'access_details',
            [
                'legend' => __('Access Details'),
                'collapsable' => false,
            ],
        );

        $button = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Update Access Data'),
                'onclick' => sprintf(
                    'OnBuyAccountObj.openAccessDataPopup(\'%s\')',
                    $this->accountUrlHelper->getUpdateCredentialsUrl((int)$this->getRequest()->getParam('id'))
                ),
                'class' => 'check onbuy_check_button primary',
            ],
        );

        $fieldset->addField(
            'update_access_data_container',
            'label',
            [
                'label' => '',
                'after_element_html' => $button->toHtml(),
            ],
        );

        $this->setForm($form);

        $id = $this->getRequest()->getParam('id');
        $this->js->add("OnBuy.formData.id = '$id';");

        $this->js->add(
            <<<JS
    require([
        'OnBuy/Account'
    ], function(){
        window.OnBuyAccountObj = new OnBuyAccount();
        OnBuyAccountObj.initObservers();
    });
JS,
        );

        return parent::_prepareForm();
    }
}
