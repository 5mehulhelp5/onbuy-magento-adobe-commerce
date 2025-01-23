<?php

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Moving;

class MoveToListingGrid extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'accountId' => (int)$this->getRequest()->getParam('account_id'),
                'siteId' => (int)$this->getRequest()->getParam('site_id'),
                'data' => [
                    'grid_url' => $this->getUrl(
                        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper::PATH_UNMANAGED_MOVE_TO_LISTING,
                        ['_current' => true]
                    ),
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
