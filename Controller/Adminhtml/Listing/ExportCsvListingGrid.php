<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing;

class ExportCsvListingGrid extends \M2E\OnBuy\Controller\Adminhtml\AbstractMain
{
    private \M2E\OnBuy\Helper\Data\FileExport $fileExportHelper;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Helper\Data\FileExport $fileExportHelper,
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->listingRepository->get((int)$id);

        $this->uiListingRuntimeStorage->setListing($listing);

        $gridName = $listing->getTitle();

        $content = $this->_view
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Listing\View\OnBuy\Grid::class)
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }
}
