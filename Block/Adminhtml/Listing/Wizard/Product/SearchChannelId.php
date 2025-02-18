<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Product;

class SearchChannelId extends \Magento\Framework\View\Element\Template
{
    public const PROGRESS_BAR_ELEMENT_ID = 'listing_wizard_product_search_channel_id_progress_bar';

    protected $_template = 'M2E_OnBuy::listing/wizard/product_search_channel_id.phtml';

    private \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelIdManager;
    private \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;
    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\SearchChannelProductIdManager $searchChannelIdManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Model\Settings $settings,
        array $data = []
    ) {
        $this->searchChannelIdManager = $searchChannelIdManager;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        $this->settings = $settings;
        parent::__construct($context, $data);
    }

    public function getLinkForSearch(): string
    {
        return $this->getUrl(
            '*/listing_wizard_search/searchChannelId',
            ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
        );
    }

    public function getProgressBarElementId(): string
    {
        return self::PROGRESS_BAR_ELEMENT_ID;
    }

    public function isNeedSearch(): bool
    {
        if (!$this->settings->getIdentifierCodeValue()) {
            return false;
        }

        $manager = $this->uiWizardRuntimeStorage->getManager();

        return !$this->searchChannelIdManager->isAllFound($manager);
    }
}
