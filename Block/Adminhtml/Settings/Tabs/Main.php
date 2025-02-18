<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Settings\Tabs;

use M2E\OnBuy\Model\Settings;

class Main extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;
    private Settings $settings;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Settings $settings,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->settings = $settings;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $configurationHelper = $this->settings;

        $textAttributes = $this->magentoAttributeHelper->filterByInputTypes(
            $this->magentoAttributeHelper->getAll(),
            ['text', 'select', 'weight']
        );

        //region Product Identifier
        $fieldset = $form->addFieldset(
            'product_settings_fieldset',
            [
                'legend' => __('Product'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'identifier_code_custom_attribute',
            'hidden',
            [
                'name' => 'identifier_code_custom_attribute',
                'value' => $configurationHelper->getIdentifierCodeValue(),
            ]
        );

        $preparedAttributes = [];

        $warningToolTip = '';

        if (
            $configurationHelper->isIdentifierCodeConfigured()
            && !$this->magentoAttributeHelper->isExistInAttributesArray(
                $configurationHelper->getIdentifierCodeValue(),
                $textAttributes
            ) && $this->getData('identifier_code_custom_attribute') != ''
        ) {
            $warningText = __(
                'Selected Magento Attribute is invalid. Please ensure that the Attribute ' .
                'exists in your Magento, has a relevant Input Type and it is included in all Attribute Sets. ' .
                'Otherwise, select a different Attribute from the drop-down.'
            );

            $warningToolTip = __(
                <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning">
    {$this->getTooltipHtml((string)$warningText)}
</span>
HTML
            );

            $attrs = ['attribute_code' => $configurationHelper->getIdentifierCodeValue()];
            $attrs['selected'] = 'selected';
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \M2E\OnBuy\Model\Settings::VALUE_MODE_ATTRIBUTE,
                'label' => $this->magentoAttributeHelper
                    ->getAttributeLabel($configurationHelper->getIdentifierCodeValue()),
            ];
        }

        foreach ($textAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $configurationHelper->isIdentifierCodeConfigured()
                && $attribute['code'] === $configurationHelper->getIdentifierCodeValue()
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \M2E\OnBuy\Model\Settings::VALUE_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'identifier_code_mode',
            self::SELECT,
            [
                'name' => 'identifier_code_mode',
                'label' => __('EAN / UPC'),
                'class' => 'onbuy-identifier-code',
                'values' => [
                    \M2E\OnBuy\Model\Settings::VALUE_MODE_NOT_SET => __('Not Set'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$configurationHelper->isIdentifierCodeConfigured()
                    ? $configurationHelper->getIdentifierCodeMode()
                    : '',
                'create_magento_attribute' => true,
                'tooltip' => __(
                    '%channel_title uses EAN/UPC to associate your Item with its catalog. ' .
                    'Select Attribute where the Product ID values are stored.',
                    [
                        'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                    ]
                ),
                'after_element_html' => $warningToolTip,
                'required' => false,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');
        //endregion

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/settings/save'),
            \M2E\OnBuy\Block\Adminhtml\Settings\Tabs::TAB_ID_MAIN
        );

        $jsSettings = json_encode(
            [
                'identifierSettings' => [
                    'valueModeAttribute' => \M2E\OnBuy\Model\Settings::VALUE_MODE_ATTRIBUTE,
                ],
            ]
        );
        $this->js->add(
            <<<JS
require([
    'OnBuy/Settings/Main'
], function(){
    window.OnBuySettingsMainObj = new OnBuySettingsMain($jsSettings);
});
JS
        );

        return parent::_beforeToHtml();
    }
}
