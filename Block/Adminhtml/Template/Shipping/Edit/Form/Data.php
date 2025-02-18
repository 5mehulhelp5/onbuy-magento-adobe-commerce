<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Template\Shipping\Edit\Form;

class Data extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
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
}
