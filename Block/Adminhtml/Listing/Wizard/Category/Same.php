<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Category;

class Same extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractContainer
{
    use \M2E\OnBuy\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('listingCategoryChooser');

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'class' => 'action-primary forward',
                'onclick' => sprintf(
                    "OnBuyListingCategoryObj.modeSameSubmitData('%s')",
                    $this->getUrl(
                        '*/listing_wizard_category/assignModeSame',
                        ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
                    ),
                ),
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );

        $this->_headerText = __('Categories');
    }

    protected function _beforeToHtml()
    {
        $this->js->add(
            <<<JS
 require([
    'OnBuy/Listing/Wizard/Category'
], function() {
    window.OnBuyListingCategoryObj = new OnBuyListingCategory(null);
});
JS,
        );

        return parent::_beforeToHtml();
    }
}
