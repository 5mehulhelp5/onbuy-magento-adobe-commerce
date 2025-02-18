<?php

namespace M2E\OnBuy\Controller\Adminhtml\Listing;

class Index extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        if ($this->isAjax()) {
            /** @var \M2E\OnBuy\Block\Adminhtml\Listing\ItemsByListing\Grid $grid */
            $grid = $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\ItemsByListing\Grid::class
            );
            $this->setAjaxContent($grid);

            return $this->getResult();
        }

        /** @var \M2E\OnBuy\Block\Adminhtml\Listing\ItemsByListing $block */
        $block = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\Listing\ItemsByListing::class
        );
        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Items By Listing'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/docs/m2e-onbuy-listings/');

        return $this->getResult();
    }
}
