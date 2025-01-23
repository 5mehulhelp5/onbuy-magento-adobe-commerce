<?php

namespace M2E\OnBuy\Block\Adminhtml\Account;

class Edit extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private ?\M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        ?\M2E\OnBuy\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        $this->accountUrlHelper = $accountUrlHelper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_onBuy_account';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $accountId = (int)$this->getRequest()->getParam('id');

        if ($this->getRequest()->getParam('close_on_save', false)) {
            if ($accountId) {
                $this->addButton('save', [
                    'label' => __('Save And Close'),
                    'onclick' => 'OnBuyAccountObj.saveAndClose()',
                    'class' => 'primary',
                ]);
            } else {
                $this->addButton('save_and_continue', [
                    'label' => __('Save And Continue Edit'),
                    'onclick' => 'OnBuyAccountObj.saveAndEditClick(\'\',\'onbuyTabs\')',
                    'class' => 'primary',
                ]);
            }

            return;
        }

        $saveButtonsProps = [];
        if ($this->account !== null) {
            $this->addButton('back', [
                'label' => __('Back'),
                'onclick' => 'OnBuyAccountObj.backClick(\'' . $this->accountUrlHelper->getIndexUrl() . '\')',
                'class' => 'back',
            ]);

            $this->addButton('delete', [
                'label' => __('Delete'),
                'onclick' => sprintf(
                    "OnBuyAccountObj.deleteClick('%s', '%s')",
                    $this->accountUrlHelper->getDeleteUrl($accountId),
                    $this->_escaper->escapeJs(\M2E\OnBuy\Model\TranslateText::getAccountDelete())
                ),
                'class' => 'delete onbuy_delete_button primary',
            ]);

            $this->addButton('refresh', [
                'label' => __('Refresh Account Data'),
                'onclick' => 'setLocation(\'' . $this->accountUrlHelper->getRefreshUrl($accountId) . '\')',
                'class' => 'onbuy_refresh_button primary',
            ]);

            $saveButtonsProps['save'] = [
                'label' => __('Save And Back'),
                'onclick' => 'OnBuyAccountObj.saveClick()',
                'class' => 'save primary',
            ];
        }

        // ---------------------------------------
        if (!empty($saveButtonsProps)) {
            $saveButtons = [
                'id' => 'save_and_continue',
                'label' => __('Save And Continue Edit'),
                'class' => 'add',
                'button_class' => '',
                'onclick' => 'OnBuyAccountObj.saveAndEditClick(\'\', \'onbuyAccountEditTabs\')',
                'class_name' => \M2E\OnBuy\Block\Adminhtml\Magento\Button\SplitButton::class,
                'options' => $saveButtonsProps,
            ];

            $this->addButton('save_buttons', $saveButtons);
        } else {
            $this->addButton('save_and_continue', [
                'label' => __('Save And Continue Edit'),
                'class' => 'add primary',
                'onclick' => 'OnBuyAccountObj.saveAndEditClick(\'\')',
            ]);
        }
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\OnBuy\Block\Adminhtml\Account\Edit\Form::class);

        return parent::_prepareLayout();
    }
}
