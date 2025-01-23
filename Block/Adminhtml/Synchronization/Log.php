<?php

namespace M2E\OnBuy\Block\Adminhtml\Synchronization;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractContainer;

class Log extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('synchronizationLog');
        $this->_controller = 'adminhtml_synchronization_log';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2E_OnBuy::magento/grid/container/only_content.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $helpBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\OnBuy\Block\Adminhtml\HelpBlock::class,
                '',
                [
                    'data' => [
                        'content' => __('The Log includes information about synchronization ' .
                            'of M2E OnBuy Connect Listings, Orders, Unmanaged Listings.'),
                    ],
                ]
            );

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
