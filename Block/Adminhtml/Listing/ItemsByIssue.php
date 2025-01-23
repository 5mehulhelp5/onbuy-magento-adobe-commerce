<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractContainer;

class ItemsByIssue extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('onBuyListingItemsByIssue');
        $this->_controller = 'adminhtml_listing_itemsByIssue';
        // ---------------------------------------

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('add');
        $this->buttonList->remove('save');
        $this->buttonList->remove('edit');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('switcher.css');

        return parent::_prepareLayout();
    }

    /**
     * @ingeritdoc
     */
    protected function _toHtml(): string
    {
        $filterBlockHtml = $this->getFilterBlockHtml();

        /** @var \M2E\OnBuy\Block\Adminhtml\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateItemsByIssueTab();
        $tabsBlockHtml = $tabsBlock->toHtml();

        return $filterBlockHtml . $tabsBlockHtml . parent::_toHtml();
    }

    private function getFilterBlockHtml(): string
    {
        $accountSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Switcher::class)
            ->setData([
                'controller_name' => $this->getRequest()->getControllerName(),
            ]);

        return <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$accountSwitcherBlock->toHtml()}
    </div>
</div>
HTML;
    }
}
