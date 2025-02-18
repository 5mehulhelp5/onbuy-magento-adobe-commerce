<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Settings;

class View extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractContainer
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

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('IdentifierForListingProducts');

        $this->prepareButtons(
            [
                'id' => 'identifier_settings_continue',
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'OnBuy/Listing/Wizard/Settings' => [
                            'urlSave' => $this->getUrl(
                                '*/listing_wizard_settings/save',
                                ['wizard_id' => $this->getWizardIdFromRequest()],
                            ),
                            'urlContinue' => $this->getUrl(
                                '*/listing_wizard_settings/completeStep',
                                ['id' => $this->getWizardIdFromRequest()],
                            ),
                        ],
                    ],
                ],
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );
    }

    protected function _toHtml()
    {
        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Settings\Tabs\Main::class,
            );

        $html = '<div id="identifier_settings">';
        $html .= $block->toHtml();
        $html .= '</div>';

        return parent::_toHtml() . $html;
    }
}
