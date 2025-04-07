<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Policy;

class View extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractContainer
{
    use \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('PolicyForListingProducts');

        $urlSave = $this->getUrl(
            '*/listing_wizard_policy/save',
            [
                'id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                'wizard_id' => $this->getWizardIdFromRequest(),
            ]
        );

        $this->prepareButtons(
            [
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => 'OnBuyListingSettingsObj.saveClick(\'' . $urlSave . '\')',
            ],
            $this->uiWizardRuntimeStorage->getManager()
        );
    }

    protected function _toHtml()
    {
        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Listing\Edit::class,
                '',
                [
                    'listing' => $this->uiListingRuntimeStorage->getListing(),
                    'id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                ],
            );

        return parent::_toHtml()
            . $block->toHtml();
    }
}
