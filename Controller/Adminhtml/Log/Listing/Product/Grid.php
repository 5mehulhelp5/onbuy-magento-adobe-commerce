<?php

namespace M2E\OnBuy\Controller\Adminhtml\Log\Listing\Product;

use M2E\OnBuy\Block\Adminhtml\Log\Listing\View;

class Grid extends \M2E\OnBuy\Controller\Adminhtml\Log\AbstractListing
{
    private \M2E\OnBuy\Helper\Data\Session $sessionHelper;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Helper\Data\Session $sessionHelper
    ) {
        parent::__construct();

        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function execute()
    {
        $listingId = $this->getRequest()->getParam(
            \M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );
        $listingProductId = $this->getRequest()->getParam(
            \M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );

        if ($listingId) {
            try {
                $listing = $this->listingRepository->get($listingId);
            } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
                $this->setJsonContent([
                    'status' => false,
                    'message' => __('Listing does not exist.'),
                ]);

                return $this->getResult();
            }
        } elseif ($listingProductId) {
            $listingProduct = $this->listingProductRepository->find($listingProductId);

            if ($listingProduct === null) {
                $this->setJsonContent([
                    'status' => false,
                    'message' => __('Listing Product does not exist.'),
                ]);

                return $this->getResult();
            }
        }

        $sessionViewMode = $this->sessionHelper->getValue(
            \M2E\OnBuy\Helper\View\OnBuy::NICK . '_log_listing_view_mode'
        );

        if ($sessionViewMode === null) {
            $sessionViewMode = View\Switcher::VIEW_MODE_SEPARATED;
        }

        $viewMode = $this->getRequest()->getParam(
            'view_mode',
            $sessionViewMode
        );

        if ($viewMode === View\Switcher::VIEW_MODE_GROUPED) {
            $gridClass = \M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\View\Grouped\Grid::class;
        } else {
            $gridClass = \M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\View\Separated\Grid::class;
        }

        $block = $this->getLayout()->createBlock($gridClass);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
