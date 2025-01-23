<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapProductPopupHtml extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $unmanagedId = $this->getRequest()->getParam('unmanaged_product_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $block = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\Listing\Mapping\View::class,
            '',
            [
                'data' => [
                    'unmanaged_product_id' => $unmanagedId,
                    'account_id' => $accountId,
                    'grid_url' => \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper::PATH_UNMANAGED_MAP_GRID,
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
