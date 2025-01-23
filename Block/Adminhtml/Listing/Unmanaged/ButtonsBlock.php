<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Unmanaged;

class ButtonsBlock extends \Magento\Backend\Block\Widget
{
    private \M2E\OnBuy\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;
    private \M2E\OnBuy\Model\InventorySync\LockManager $accountLockManager;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;
    private \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper,
        \M2E\OnBuy\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage,
        \M2E\OnBuy\Model\InventorySync\LockManager $accountLockManager,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $otherRepository,
        \M2E\OnBuy\Model\UnmanagedProduct\Ui\UrlHelper $urlHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
        $this->accountLockManager = $accountLockManager;
        $this->unmanagedRepository = $otherRepository;
        $this->accountUrlHelper = $accountUrlHelper;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('listing/unmanaged/buttons_block.phtml');
    }

    public function getUrlForOpenSettings(): string
    {
        return $this->accountUrlHelper->getEditUrl(
            (int)$this->getAccount()->getId(),
            ['close_on_save' => true, 'tab' => 'listingOther']
        );
    }

    public function getUrlForResetUnmanaged(): string
    {
        return $this->urlHelper->getUnmanagedResetUrl(['account_id' => $this->getAccount()->getId()]);
    }

    public function isNeedShowOpenSettingsButton(): bool
    {
        return !$this->getAccount()->getUnmanagedListingSettings()->isSyncEnabled();
    }

    public function isNeedShowResetButton(): bool
    {
        return !$this->isDownloadUnmanagedInProcess()
            && $this->unmanagedRepository->isExistForAccount($this->getAccount()->getId());
    }

    public function isNeedShowInProgressButton(): bool
    {
        return $this->isDownloadUnmanagedInProcess();
    }

    private function isDownloadUnmanagedInProcess(): bool
    {
        return $this->accountLockManager->isExistByAccount($this->getAccount());
    }

    // ----------------------------------------

    private function getAccount(): \M2E\OnBuy\Model\Account
    {
        return $this->uiAccountRuntimeStorage->getAccount();
    }
}
