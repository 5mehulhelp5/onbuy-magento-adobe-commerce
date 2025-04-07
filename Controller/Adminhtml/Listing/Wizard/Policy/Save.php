<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Listing\Wizard\Policy;

use M2E\OnBuy\Model\Listing;

class Save extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Listing\UpdateService $listingUpdateService;

    public function __construct(
        \M2E\OnBuy\Model\Listing\UpdateService $listingUpdateService,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
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

        $id = (int)$this->getRequest()->getParam('id');
        $wizardId = $this->getRequest()->getParam('wizard_id');

        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        $missingPolicies = [];
        foreach (Listing::REQUIRED_POLICIES as $policy => $paramName) {
            if (!$this->getRequest()->getParam($paramName)) {
                $missingPolicies[] = $policy;
            }
        }

        if (!empty($missingPolicies)) {
            foreach ($missingPolicies as $policy) {
                $this->getMessageManager()->addErrorMessage(
                    __("The %1 Policy is required. Please add a valid %1 Policy to proceed.", $policy)
                );
            }

            $url = $this->getUrl('*/listing_wizard_policy/view', ['id' => $wizardId]);
            return $this->_redirect($url);
        }

        try {
            $this->listingUpdateService->update($listing, $post);
        } catch (\M2E\OnBuy\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        $urlCompleteStep = $this->getUrl(
            '*/listing_wizard_policy/completeStep',
            ['id' => $wizardId]
        );

        return $this->_redirect($urlCompleteStep);
    }
}
