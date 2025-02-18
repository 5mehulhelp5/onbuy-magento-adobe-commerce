<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\Create\Templates;

use M2E\OnBuy\Model\Listing;
use M2E\OnBuy\Model\Policy\Manager as TemplateManager;
use M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat\CollectionFactory as SellingFormatCollectionFactory;
use M2E\OnBuy\Model\ResourceModel\Policy\Shipping\CollectionFactory as ShippingCollectionFactory;
use M2E\OnBuy\Model\ResourceModel\Policy\Synchronization\CollectionFactory as SynchronizationCollectionFactory;
use M2E\OnBuy\Model\ResourceModel\Policy\Shipping as ShippingResource;

class Form extends \M2E\OnBuy\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected ?Listing $listing = null;
    private \M2E\OnBuy\Helper\Data\Session $sessionDataHelper;
    private Listing\Repository $listingRepository;
    private SellingFormatCollectionFactory $sellingFormatCollectionFactory;
    private SynchronizationCollectionFactory $synchronizationCollectionFactory;
    private ShippingCollectionFactory $shippingCollectionFactory;

    public function __construct(
        ShippingCollectionFactory $shippingCollectionFactory,
        SellingFormatCollectionFactory $sellingFormatCollectionFactory,
        SynchronizationCollectionFactory $synchronizationCollectionFactory,
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\OnBuy\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingRepository = $listingRepository;
        $this->sellingFormatCollectionFactory = $sellingFormatCollectionFactory;
        $this->synchronizationCollectionFactory = $synchronizationCollectionFactory;
        $this->shippingCollectionFactory = $shippingCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getUrl('*/listing/save'),
                ],
            ]
        );

        $formData = $this->getListingData();

        $form->addField(
            'site_id',
            'hidden',
            [
                'value' => $formData['site_id'],
            ]
        );

        $form->addField(
            'store_id',
            'hidden',
            [
                'value' => $formData['store_id'],
            ]
        );

        $fieldset = $form->addFieldset(
            'selling_settings',
            [
                'legend' => __('Selling'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'template_selling_format_messages',
            self::CUSTOM_CONTAINER,
            [
                'style' => 'display: block;',
                'css_class' => 'OnBuy-fieldset-table no-margin-bottom',
            ]
        );

        $sellingFormatTemplates = $this->getSellingFormatTemplates();
        $style = count($sellingFormatTemplates) === 0 ? 'display: none' : '';

        $templateSellingFormatValue = $formData['template_selling_format_id'];
        if (empty($templateSellingFormatValue) && !empty($sellingFormatTemplates)) {
            $templateSellingFormatValue = reset($sellingFormatTemplates)['value'];
        }

        $templateSellingFormat = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_selling_format_id',
                    'name' => 'template_selling_format_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $sellingFormatTemplates),
                    'value' => $templateSellingFormatValue,
                    'required' => true,
                ],
            ]
        );
        $templateSellingFormat->setForm($form);

        $style = count($sellingFormatTemplates) === 0 ? '' : 'display: none';
        $noPoliciesAvailableText = __('No Policies available.');
        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');
        $fieldset->addField(
            'template_selling_format_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Selling Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_selling_format_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="OnBuyListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
            $('template_selling_format_id').value,
            OnBuyListingSettingsObj.newSellingFormatTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="OnBuyListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
        OnBuyListingSettingsObj.newSellingFormatTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $fieldset->addField(
            'condition',
            self::SELECT,
            [
                'name' => 'condition',
                'label' => $this->__('Condition'),
                'values' => $this->getConditionValues(),
                    'value' => $formData['condition'],
                'tooltip' => $this->__(
                    <<<HTML
                    <p>Specify the condition that best describes the current state of your product.</p><br>

                    <p>By providing accurate information about the product condition, you improve the visibility
                    of your listings, ensure fair pricing, and increase customer satisfaction.</p>
HTML
                ),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'condition_note',
            'textarea',
            [
                'container_id' => 'condition_note',
                'name' => 'condition_note',
                'label' => $this->__('Condition Note'),
                'maxlength' => 255,
                'rows' => 5,
                'value' => $formData['condition_note'],
                'tooltip' => $this->__('Short Description of Item(s) Condition.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'synchronization_settings',
            [
                'legend' => __('Synchronization'),
                'collapsable' => false,
            ]
        );

        $synchronizationTemplates = $this->getSynchronizationTemplates();
        $style = count($synchronizationTemplates) === 0 ? 'display: none' : '';

        $templateSynchronizationValue = $formData['template_synchronization_id'];
        if (empty($templateSynchronizationValue) && !empty($synchronizationTemplates)) {
            $templateSynchronizationValue = reset($synchronizationTemplates)['value'];
        }

        $templateSynchronization = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_synchronization_id',
                    'name' => 'template_synchronization_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $synchronizationTemplates),
                    'value' => $templateSynchronizationValue,
                    'required' => true,
                ],
            ]
        );
        $templateSynchronization->setForm($form);

        $style = count($synchronizationTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_synchronization_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Synchronization Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_synchronization_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="OnBuyListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
            $('template_synchronization_id').value,
            OnBuyListingSettingsObj.newSynchronizationTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="OnBuyListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
        OnBuyListingSettingsObj.newSynchronizationTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $fieldset = $form->addFieldset(
            'shipping_settings',
            [
                'legend' => __('Shipping'),
                'collapsable' => false,
            ]
        );

        $accountId = (int)$formData['account_id'];
        $siteId = (int)$formData['site_id'];
        $shippingTemplates = $this->getShippingTemplates($siteId);
        $style = count($shippingTemplates) === 0 ? 'display: none' : '';

        $shippingTemplatesValue = $formData['template_shipping_id'];

        $templateShipping = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_shipping_id',
                    'name' => 'template_shipping_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ' '], $shippingTemplates),
                    'value' => $shippingTemplatesValue,
                    'required' => false,
                ],
            ]
        );
        $templateShipping->setForm($form);

        $style = count($shippingTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_shipping_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Shipping Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => false,
                'text' => <<<HTML
    <span id="template_shipping_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateShipping->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_shipping_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="OnBuyListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SHIPPING)}',
            $('template_shipping_id').value,
            OnBuyListingSettingsObj.newShippingTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_shipping_template_link" href="javascript: void(0);"
        onclick="OnBuyListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl( TemplateManager::TEMPLATE_SHIPPING, $accountId, $siteId)}',
        OnBuyListingSettingsObj.newShippingTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->jsPhp->addConstants(
            [
                '\M2E\OnBuy\Model\Listing::CONDITION_NEW' => \M2E\OnBuy\Model\Listing::CONDITION_NEW,
            ]
        );
        $formData = $this->getListingData();

        $this->jsUrl->addUrls(
            [
                'templateCheckMessages' => $this->getUrl('*/policy/checkMessages'),
                'getShippingTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Policy_Shipping',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'account_id' => $formData['account_id'],
                        'site_id' => $formData['site_id'],
                    ]
                ),
                'getReturnPolicyTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'OnBuy_Template_ReturnPolicy',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'is_custom_template' => 0,
                    ]
                ),
                'getSellingFormatTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Policy_SellingFormat',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'is_custom_template' => 0,
                    ]
                ),
                'getSynchronizationTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Policy_Synchronization',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'is_custom_template' => 0,
                    ]
                ),
            ]
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'OnBuy/TemplateManager',
        'OnBuy/Listing/Settings'
    ], function(){
        TemplateManagerObj = new TemplateManager();
        OnBuyListingSettingsObj = new OnBuyListingSettings();
        OnBuyListingSettingsObj.initObservers();
    });
JS
        );

        return parent::_prepareLayout();
    }

    public function getDefaultFieldsValues()
    {
        return [
            'template_selling_format_id' => '',
            'template_description_id' => '',
            'template_synchronization_id' => '',
            'template_shipping_id' => '',
            'condition' => '',
            'condition_note' => '',
        ];
    }

    protected function getListingData(): ?array
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = array_merge($this->getListing()->getData(), $this->getListing()->getData());
        } else {
            $data = $this->sessionDataHelper->getValue(Listing::CREATE_LISTING_SESSION_DATA);
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    protected function getListing(): ?Listing
    {
        $listingId = $this->getRequest()->getParam('id');
        if ($this->listing === null && $listingId) {
            $this->listing = $this->listingRepository->get((int)$listingId);
        }

        return $this->listing;
    }

    protected function getSellingFormatTemplates()
    {
        $collection = $this->sellingFormatCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat::COLUMN_ID,
                'label' => \M2E\OnBuy\Model\ResourceModel\Policy\SellingFormat::COLUMN_TITLE,
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    protected function getSynchronizationTemplates(): array
    {
        $collection = $this->synchronizationCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization::COLUMN_ID,
                'label' => \M2E\OnBuy\Model\ResourceModel\Policy\Synchronization::COLUMN_TITLE,
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getShippingTemplates(int $storeId): array
    {
        $collection = $this->shippingCollectionFactory->create();
        $collection->addFieldToFilter(ShippingResource::COLUMN_SITE_ID, ['eq' => $storeId]);
        $collection->setOrder(ShippingResource::COLUMN_TITLE, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                   ->columns(
                       [
                           'value' => ShippingResource::COLUMN_ID,
                           'label' => ShippingResource::COLUMN_TITLE,
                       ]
                   );

        $result = $collection->toArray();

        return $result['items'];
    }

    protected function getAddNewUrl($nick, int $accountId = null, int $siteId = null)
    {
        $params = [
            'wizard' => $this->getRequest()->getParam('wizard'),
            'nick' => $nick,
            'close_on_save' => 1,
        ];

        if ($accountId !== null) {
            $params['account_id'] = $accountId;
        }

        if ($siteId !== null) {
            $params['site_id'] = $siteId;
        }

        return $this->getUrl('*/policy/newAction', $params);
    }

    protected function getEditUrl($nick)
    {
        return $this->getUrl(
            '*/policy/edit',
            [
                'wizard' => $this->getRequest()->getParam('wizard'),
                'nick' => $nick,
                'close_on_save' => 1,
            ]
        );
    }

    /**
     * @return array[]
     */
    private function getConditionValues(): array
    {
        return  [
            [
                'value' => Listing::CONDITION_NEW,
                'label' => $this->__('New'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED_DIAMOND,
                'label' => $this->__('Refurbished (Diamond)'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED_PLATINUM,
                'label' => $this->__('Refurbished (Platinum)'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED_GOLD,
                'label' => $this->__('Refurbished (Gold)'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED_SILVER,
                'label' => $this->__('Refurbished (Silver)'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED_BRONZE,
                'label' => $this->__('Refurbished (Bronze)'),
            ],
            [
                'value' => Listing::CONDITION_REFURBISHED,
                'label' => $this->__('Refurbished'),
            ],
        ];
    }
}
