<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\OnBuy\Block\Adminhtml\Listing\Switcher
 */
abstract class Switcher extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setAddListingUrl('*/listing_create/index');

        $this->setTemplate('M2E_OnBuy::listing/switcher.phtml');
    }

    //########################################
}
