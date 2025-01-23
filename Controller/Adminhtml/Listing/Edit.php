<?php

namespace M2E\OnBuy\Controller\Adminhtml\Listing;

class Edit extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::listings_items');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError($exception->getMessage());

            return $this->_redirect('*/listing/index');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Listing\Edit::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __(
                'Edit M2E OnBuy Connect Listing "%listing_title" Settings',
                ['listing_title' => $listing->getTitle()]
            ),
        );

        return $this->getResult();
    }
}
