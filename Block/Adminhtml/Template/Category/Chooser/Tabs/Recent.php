<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Tabs;

class Recent extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('onBuyCategoryChooserCategoryRecent');
        $this->setTemplate('template/category/chooser/tabs/recent.phtml');
    }
}
