<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapGrid extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\OnBuy\Block\Adminhtml\Listing\Mapping\Grid::class,
            '',
            [
                'data' => [
                    'unmanaged_product_id' => (int)$this->getRequest()->getParam('unmanaged_product_id'),
                    'grid_url' => \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper::PATH_UNMANAGED_MAP_GRID,
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
