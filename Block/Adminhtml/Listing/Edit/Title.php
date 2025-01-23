<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Edit;

class Title extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractContainer
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_listing';
        $this->_mode = 'edit_title';

        parent::_construct();
    }
}
