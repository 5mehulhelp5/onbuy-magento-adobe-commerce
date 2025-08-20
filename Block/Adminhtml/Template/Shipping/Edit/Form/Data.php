<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Template\Shipping\Edit\Form;

use M2E\OnBuy\Model\Policy\Shipping;

class Data extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->siteRepository = $siteRepository;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Data
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'shipping_id',
            'hidden',
            [
                'name' => 'shipping[id]',
                'value' => $formData['id'] ?? '',
            ]
        );

        $form->addField(
            'shipping_title',
            'hidden',
            [
                'name' => 'shipping[title]',
                'value' => $this->getTitle(),
            ]
        );

        $form->addField(
            'handling_time',
            'hidden',
            [
                'name' => 'shipping[handling_time]',
                'value' => $formData['handling_time'],
            ]
        );

        $form->addField(
            'handling_time_attribute',
            'hidden',
            [
                'name' => 'shipping[handling_time_attribute]',
                'value' => $formData['handling_time_attribute'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_shipping_edit_form',
            [
                'legend' => __('Channel'),
                'collapsable' => false,
            ]
        );

        if (isset($formData['site_id'])) {
            $fieldset->addField(
                'site_id_hidden',
                'hidden',
                [
                    'name' => 'shipping[site_id]',
                    'value' => $formData['site_id'],
                ]
            );
        }

        $fieldset->addField(
            'site_id',
            self::SELECT,
            [
                'name' => 'shipping[site_id]',
                'label' => $this->__('Site'),
                'disabled' => isset($formData['site_id']),
                'required' => true,
            ]
        );

        $style = empty($formData['site_id']) ? 'display: none;' : '';

        $fieldset->addField(
            'delivery_template_id',
            self::SELECT,
            [
                'name' => 'shipping[delivery_template_id]',
                'label' => __('Template'),
                'title' => __('Template'),
                'required' => true,
                'style' => 'max-width: 30%;',
                'after_element_html' => $this->createButtonsBlock(
                    [
                        $this->getRefreshButtonHtml(
                            'refresh_templates',
                            'OnBuyTemplateShippingObj.updateDeliveryTemplates(true);',
                            $style
                        ),
                    ],
                    $style
                ),
            ]
        );

        $handlingModeOptions = $this->getHandlingTimeOptions();
        $handlingModeOptions[] = $this->getAttributesOptions(
            Shipping::HANDLING_TIME_MODE_ATTRIBUTE,
            (int)$formData['handling_time_mode'],
            $formData['handling_time_attribute'] ?? ''
        );

        $fieldset->addField(
            'handling_time_mode',
            self::SELECT,
            [
                'name' => 'shipping[handling_time_mode]',
                'label' => __('Handling Time'),
                'title' => __('Handling Time'),
                'values' => $handlingModeOptions,
                'create_magento_attribute' => false,
                'tooltip' => __(
                    'The number of days within which the item will be dispatched.'
                ),
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,select');

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTitle()
    {
        $template = $this->globalDataHelper->getValue('onbuy_template_shipping');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        $template = $this->globalDataHelper->getValue('onbuy_template_shipping');

        if ($template === null || $template->getId() === null) {
            $formData = [];
            $siteId = $this->getRequest()->getParam('site_id', false);
            if ($siteId !== false) {
                $formData['site_id'] = $siteId;
            }

            return $formData;
        }

        return $template->getData();
    }

    private function getDefault(): array
    {
        return [
            'delivery_template_id' => '',
            'handling_time' => '',
            'handling_time_mode' => \M2E\OnBuy\Model\Policy\Shipping::HANDLING_TIME_MODE_NOT_SET,
            'handling_time_attribute' => '',
        ];
    }

    public function getSiteDataOptions(int $accountId): array
    {
        $sites = $this->siteRepository->findForAccount($accountId);

        $optionsResult = [];
        foreach ($sites as $site) {
            $optionsResult[] = [
                'value' => $site['id'],
                'label' => $site->getName()
            ];
        }

        return $optionsResult;
    }

    protected function _toHtml()
    {
        $formData = $this->getFormData();
        $currentAccountId = $formData['account_id'] ?? null;
        $currentSiteId = $formData['site_id'] ?? null;
        $currentDeliveryTemplateId = $formData['delivery_template_id'] ?? null;

        $urlGetSites = $this->getUrl('*/site/getSitesForAccount');
        $urlGetTemplates = $this->getUrl('*/policy_shipping/deliveryTemplateList');

        $this->jsPhp->addConstants(
            [
                '\M2E\OnBuy\Model\Policy\Shipping::HANDLING_TIME_MODE_VALUE' => Shipping::HANDLING_TIME_MODE_VALUE,
                '\M2E\OnBuy\Model\Policy\Shipping::HANDLING_TIME_MODE_ATTRIBUTE' => Shipping::HANDLING_TIME_MODE_ATTRIBUTE,
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'OnBuy/Template/Shipping'
        ], function() {
    window.OnBuyTemplateShippingObj = new OnBuyTemplateShipping({
            accountId: '$currentAccountId',
            siteId: '$currentSiteId',
            deliveryTemplateId: '$currentDeliveryTemplateId',
            urlGetTemplates: '$urlGetTemplates',
            urlGetSites: '$urlGetSites'
        });
    OnBuyTemplateShippingObj.initObservers();
    });
JS
        );

        return parent::_toHtml();
    }

    /**
     * @param string[] $actions
     *
     * @return string
     */
    private function createButtonsBlock(array $actions, string $style): string
    {
        $formattedActions = [];
        /** @var string $action */
        foreach ($actions as $action) {
            $formattedActions[] = sprintf('<span class="action">%s</span>', $action);
        }

        return sprintf(
            '<span class="actions" style="%s">%s</span>',
            $style,
            implode(' ', $formattedActions)
        );
    }

    private function getRefreshButtonHtml(string $id, string $onClick, string $style): string
    {
        $data = [
            'id' => $id,
            'label' => __('Refresh Templates'),
            'onclick' => $onClick,
            'class' => 'refresh_status primary',
            'style' => $style,
        ];

        return $this->getLayout()
                    ->createBlock(\M2E\OnBuy\Block\Adminhtml\Magento\Button::class)
                    ->setData($data)
                    ->toHtml();
    }

    public function getHandlingTimeOptions(): array
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $selectedMode = (int)($formData['handling_time_mode']);
        $options[] = [
            'value' => Shipping::HANDLING_TIME_MODE_NOT_SET,
            'label' => __('Not Set'),
            'attrs' => $selectedMode === Shipping::HANDLING_TIME_MODE_NOT_SET
                ? ['selected' => 'selected']
                : [],
        ];

        $days = [1, 2, 3, 4, 5, 6, 7, 10];

        if (!empty($formData['handling_time']) && !in_array((int)$formData['handling_time'], $days, true)) {
            $days[] = (int)$formData['handling_time'];
            sort($days);
        }

        foreach ($days as $day) {
            $handlingOptions[] = [
                "handling_time_value" => (string)$day,
                "title" => $day . " Business Day" . ($day > 1 ? "s" : "")
            ];
        }

        foreach ($handlingOptions as $handlingOption) {
            $label = (string)__($handlingOption['title']);

            $tmpOption = [
                'value' => Shipping::HANDLING_TIME_MODE_VALUE,
                'label' => $label,
                'attrs' => ['attribute_code' => $handlingOption['handling_time_value']],
            ];

            if (
                $formData['handling_time_mode'] == Shipping::HANDLING_TIME_MODE_VALUE &&
                $handlingOption['handling_time_value'] == $formData['handling_time']
            ) {
                $tmpOption['attrs']['selected'] = 'selected';
            }

            $options[] = $tmpOption;
        }

        return $options;
    }

    public function getAttributesOptions(
        int $attributeValue,
        int $handlingTimeMode,
        string $handlingTimeAttribute
    ): array {
        $options = [
            'value' => [],
            'label' => __('Magento Attribute'),
            'attrs' => ['is_magento_attribute' => true],
        ];

        foreach ($this->magentoAttributeHelper->getAll() as $attribute) {
            $tmpOption = [
                'value' => $attributeValue,
                'label' => ($attribute['label']),
                'attrs' => ['attribute_code' => $attribute['code']],
            ];

            if (
                $handlingTimeMode === Shipping::HANDLING_TIME_MODE_ATTRIBUTE
                && $attribute['code'] === $handlingTimeAttribute
            ) {
                $tmpOption['attrs']['selected'] = 'selected';
            }

            $options['value'][] = $tmpOption;
        }

        return $options;
    }
}
