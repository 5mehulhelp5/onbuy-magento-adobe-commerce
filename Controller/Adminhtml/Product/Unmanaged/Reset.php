<?php

declare(strict_types=1);

namespace M2E\OnBuy\Controller\Adminhtml\Product\Unmanaged;

class Reset extends \M2E\OnBuy\Controller\Adminhtml\AbstractListing
{
    private \M2E\OnBuy\Model\UnmanagedProduct\Reset $listingOtherReset;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\UnmanagedProduct\Reset $listingOtherReset,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper
    ) {
        parent::__construct();
        $this->accountRepository = $accountRepository;
        $this->listingOtherReset = $listingOtherReset;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        try {
            $account = $this->accountRepository->get($accountId);
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->_redirect($this->urlHelper->getGridUrl());
        }

        $this->listingOtherReset->process($account);

        $this->messageManager->addSuccessMessage(
            __('Unmanaged Listings were reset.')
        );

        return $this->_redirect($this->urlHelper->getGridUrl(['account' => $accountId]));
    }
}
