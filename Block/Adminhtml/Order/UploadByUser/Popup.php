<?php

namespace M2E\OnBuy\Block\Adminhtml\Order\UploadByUser;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Popup extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('orderUploadByUserGrid');

        $this->_controller = 'adminhtml_order_uploadByUser';
        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2E_OnBuy::magento/grid/container/only_content.phtml');
    }

    public function getGridHtml()
    {
        return '<div id="uploadByUser_messages" style="margin-top: 10px;"></div>'
            . $this->getHelpHtml()
            . parent::getGridHtml();
    }

    private function getHelpHtml(): string
    {
        $helpText = __(
            '%extension_title provides an automatic order synchronization as basic functionality.
Use manual order import as an alternative only in <a href="%url" target="_blank">these cases</a>.',
            [
                'extension_title' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                'url' => 'https://help.m2epro.com/support/solutions/articles/9000199899',
            ]
        );

        $helpBlock = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\HelpBlock::class,
            '',
            [
                'data' => [
                    'content' => $helpText,
                    'style' => 'margin-top: 15px;',
                    'title' => __('Order Reimport'),
                ],
            ]
        );

        return $helpBlock->toHtml();
    }
}
