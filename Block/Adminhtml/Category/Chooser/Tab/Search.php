<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Category\Chooser\Tab;

class Search extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('category/chooser/tab/search.phtml');
    }
}
