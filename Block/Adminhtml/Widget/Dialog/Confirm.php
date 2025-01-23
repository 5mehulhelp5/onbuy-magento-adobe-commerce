<?php

namespace M2E\OnBuy\Block\Adminhtml\Widget\Dialog;

/**
 * Class \M2E\OnBuy\Block\Adminhtml\Widget\Dialog\Confirm
 */
class Confirm extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('widgetConfirm');
        // ---------------------------------------

        $this->setTemplate('widget/dialog/confirm.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = [
            'class' => 'ok_button',
            'label' => __('Confirm'),
            'onclick' => 'Dialog.okCallback();',
        ];
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Magento\Button::class)->setData($data);
        $this->setChild('ok_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################
}
