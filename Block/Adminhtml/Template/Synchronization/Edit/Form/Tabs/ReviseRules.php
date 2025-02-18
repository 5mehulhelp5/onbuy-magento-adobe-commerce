<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Synchronization\Edit\Form\Tabs;

class ReviseRules extends AbstractTab
{
    private \M2E\OnBuy\Model\Policy\Synchronization\Builder $synchronizationBuilder;

    public function __construct(
        \M2E\OnBuy\Model\Policy\Synchronization\Builder $synchronizationBuilder,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct(
            $globalDataHelper,
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->synchronizationBuilder = $synchronizationBuilder;
    }

    protected function _prepareForm()
    {
        $default = $this->synchronizationBuilder->getDefaultData();
        $formData = $this->getFormData();

        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'template_synchronization_form_data_revise',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Specify which Channel data should be automatically revised ' .
                    'by %extension_title.</p><br> <p>Selected Item Properties will be automatically updated based ' .
                    'on the changes in related Magento Attributes or Policy Templates.</p><br>' .
                    '<p>More detailed information on how to work with this Page can be found ' .
                    '<a href="%url" target="_blank" class="external-link">here</a>.</p>',
                    [
                        'extension_title' => \M2E\OnBuy\Helper\Module::getExtensionTitle(),
                        'url' => 'https://docs-m2.m2epro.com/docs/synchronization-policy-for-onbuy/'
                    ],
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_synchronization_form_data_revise_products',
            [
                'legend' => __('Revise Conditions'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'revise_update_qty',
            self::SELECT,
            [
                'name' => 'synchronization[revise_update_qty]',
                'label' => __('Quantity'),
                'value' => $formData['revise_update_qty'],
                'values' => [
                    1 => __('Yes'),
                ],
                'disabled' => true,
            ]
        );

        $fieldset->addField(
            'revise_update_qty_max_applied_value_mode',
            self::SELECT,
            [
                'container_id' => 'revise_update_qty_max_applied_value_mode_tr',
                'name' => 'synchronization[revise_update_qty_max_applied_value_mode]',
                'label' => __('Conditional Revise'),
                'value' => $formData['revise_update_qty_max_applied_value_mode'],
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Revise When Less or Equal to'),
                ],
            ]
        )->setAfterElementHtml(
            <<<HTML
<input name="synchronization[revise_update_qty_max_applied_value]" id="revise_update_qty_max_applied_value"
       value="{$formData['revise_update_qty_max_applied_value']}" type="text"
       style="width: 72px; margin-left: 10px;"
       class="input-text admin__control-text required-entry OnBuy-validate-qty _required" />
HTML
        );

        $fieldset->addField(
            'revise_update_qty_max_applied_value_line_tr',
            self::SEPARATOR,
            []
        );

        $fieldset->addField(
            'revise_update_price',
            self::SELECT,
            [
                'name' => 'synchronization[revise_update_price]',
                'label' => __('Price'),
                'value' => $formData['revise_update_price'],
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
            ]
        );

        $form->addField(
            'revise_qty_max_applied_value_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => __(
                    '<br/>Disabling this option might affect synchronization performance. ' .
                    'Please read <a href="%url" target="_blank">this article</a> before using the option.',
                    ['url' => 'https://help.m2epro.com/support/solutions/articles/9000200401']
                ),
                'style' => 'display: none;',
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
