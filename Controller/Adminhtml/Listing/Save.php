<?php

namespace M2E\OnBuy\Controller\Adminhtml\Listing;

class Save extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\OnBuy\Model\Listing\UpdateService $listingUpdateService;

    public function __construct(
        \M2E\OnBuy\Model\Listing\UpdateService $listingUpdateService,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\Core\Helper\Url $urlHelper
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->urlHelper = $urlHelper;
        $this->listingUpdateService = $listingUpdateService;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_OnBuy::listings_items');
    }

    public function execute()
    {
        if (!$post = $this->getRequest()->getParams()) {
            $this->_redirect('*/listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/listing/index');
        }

        try {
            $this->listingUpdateService->update($listing, $post);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/listing/index');
        }

        $this->getMessageManager()->addSuccess(__('The Listing was saved.'));

        $redirectUrl = $this->urlHelper
            ->getBackUrl(
                'list',
                [],
                ['edit' => ['id' => $id]]
            );

        return $this->_redirect($redirectUrl);
    }
}
