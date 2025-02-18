<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\Template;

use M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock;

class Switcher extends AbstractBlock
{
    public const MODE_LISTING_PRODUCT = 1;
    public const MODE_COMMON = 2;

    public const MAX_TEMPLATE_ITEMS_COUNT = 10000;

    protected $_template = 'onbuy/listing/template/switcher.phtml';

    private $templates = null;
    /** @var \M2E\OnBuy\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\OnBuy\Model\Policy\ManagerFactory $templateManagerFactory;

    public function __construct(
        \M2E\OnBuy\Model\Policy\ManagerFactory $templateManagerFactory,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        $this->templateManagerFactory = $templateManagerFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->setId('onbuyListingTemplateSwitcher');
        parent::_construct();
    }

    //########################################

    public function getHeaderText()
    {
        if ($this->getData('custom_header_text')) {
            return $this->getData('custom_header_text');
        }

        $title = '';

        switch ($this->getTemplateNick()) {
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT:
                $title = __('Selling');
                break;
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_DESCRIPTION:
                $title = __('Description');
                break;
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION:
                $title = __('Synchronization');
                break;
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING:
                $title = __('Shipping');
                break;
        }

        return $title;
    }

    //########################################

    public function getHeaderWidth()
    {
        switch ($this->getTemplateNick()) {
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT:
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_DESCRIPTION:
                $width = 250;
                break;

            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION:
                $width = 170;
                break;

            default:
                $width = 130;
                break;
        }

        return $width;
    }

    //########################################

    public function getTemplateNick()
    {
        if (!isset($this->_data['template_nick'])) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Template nick is not defined.');
        }

        return $this->_data['template_nick'];
    }

    public function getTemplateMode()
    {
        $templateMode = $this->globalDataHelper->getValue(
            'onbuy_template_mode_' . $this->getTemplateNick()
        );

        if ($templateMode === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Template Mode is not initialized.');
        }

        return $templateMode;
    }

    public function getTemplateId()
    {
        $template = $this->getTemplateObject();

        if ($template === null) {
            return null;
        }

        return $template->getId();
    }

    public function getTemplateObject()
    {
        $template = $this->globalDataHelper->getValue('onbuy_template_' . $this->getTemplateNick());

        if ($template !== null && $template->getId() !== null) {
            return $template;
        }

        return null;
    }

    // ---------------------------------------

    public function isTemplateModeParentForced()
    {
        $key = 'onbuy_template_force_parent_' . $this->getTemplateNick();
        $forcedParent = $this->globalDataHelper->getValue($key);

        return (bool)$forcedParent;
    }

    public function isTemplateModeParent()
    {
        return $this->getTemplateMode() == \M2E\OnBuy\Model\Policy\Manager::MODE_PARENT;
    }

    public function isTemplateModeCustom()
    {
        return $this->getTemplateMode() == \M2E\OnBuy\Model\Policy\Manager::MODE_CUSTOM;
    }

    public function isTemplateModeTemplate()
    {
        return $this->getTemplateMode() == \M2E\OnBuy\Model\Policy\Manager::MODE_TEMPLATE;
    }

    //########################################

    public function getFormDataBlock()
    {
        $blockName = null;

        switch ($this->getTemplateNick()) {
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SELLING_FORMAT:
                $blockName = \M2E\OnBuy\Block\Adminhtml\Template\SellingFormat\Edit\Form\Data::class;
                break;
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SYNCHRONIZATION:
                $blockName = \M2E\OnBuy\Block\Adminhtml\Template\Synchronization\Edit\Form\Data::class;
                break;
            case \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING:
                $blockName = \M2E\OnBuy\Block\Adminhtml\Template\Shipping\Edit\Form\Data::class;
                break;
        }

        if ($blockName === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic(
                sprintf('Form data Block for Template nick "%s" is unknown.', $this->getTemplateNick())
            );
        }

        $parameters = [
            'is_custom' => false,
            'custom_title' => $this->globalDataHelper->getValue('onbuy_custom_template_title'),
            'policy_localization' => $this->getData('policy_localization'),
        ];

        return $this->getLayout()->createBlock($blockName, '', ['data' => $parameters]);
    }

    public function getFormDataBlockHtml($templateDataForce = false)
    {
        $nick = $this->getTemplateNick();

        if ($this->isTemplateModeCustom() || $templateDataForce) {
            $html = $this->getFormDataBlock()->toHtml();
            $style = '';
        } else {
            $html = '';
            $style = 'display: none;';
        }

        return <<<HTML
<div id="template_{$nick}_data_container" class="template-data-container" style="{$style}">
    {$html}
</div>
HTML;
    }

    //########################################

    public function canDisplaySwitcher()
    {
        if (!$this->canDisplayUseDefaultOption() && $this->getTemplatesCount() === 0) {
            return false;
        }

        return true;
    }

    public function canDisplayUseDefaultOption()
    {
        $displayUseDefaultOption = $this->globalDataHelper->getValue('onbuy_display_use_default_option');

        if ($displayUseDefaultOption === null) {
            return true;
        }

        return (bool)$displayUseDefaultOption;
    }

    //########################################

    public function getTemplates()
    {
        if ($this->templates !== null) {
            return $this->templates;
        }

        $collection = $this->getTemplatesCollection();
        $collection->getSelect()->limit(self::MAX_TEMPLATE_ITEMS_COUNT);

        $this->templates = $collection->getItems();

        $currentTemplateOfListing = $this->getTemplateObject();
        if (!empty($currentTemplateOfListing) && !$this->isExistTemplate($currentTemplateOfListing->getId())) {
            $this->templates[$currentTemplateOfListing->getId()] = $currentTemplateOfListing;
        }

        return $this->templates;
    }

    protected function isExistTemplate($templateId)
    {
        if (array_key_exists($templateId, $this->templates)) {
            return true;
        }

        return false;
    }

    public function getTemplatesCount()
    {
        return $this->getTemplatesCollection()->getSize();
    }

    protected function getTemplatesCollection()
    {
        $manager = $this->templateManagerFactory->create();

        $manager->setTemplate($this->getTemplateNick());

        $collection = $manager->getTemplateModel()
                              ->getCollection()
                              ->addFieldToFilter('is_custom_template', 0)
                              ->setOrder('title', 'ASC');

        return $collection;
    }

    public function getSwitcherId()
    {
        $nick = $this->getTemplateNick();

        return "template_{$nick}";
    }

    public function getSwitcherName()
    {
        $nick = $this->getTemplateNick();

        return "template_{$nick}";
    }

    //########################################

    public function getButtonsHtml()
    {
        $html = $this->getChildHtml('save_custom_as_template');
        $nick = $this->getTemplateNick();

        return <<<HTML
<div id="template_{$nick}_buttons_container">
    {$html}
</div>
HTML;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $nick = $this->getTemplateNick();
        $data = [
            'class' => 'action primary save-custom-template-' . $nick,
            'label' => __('Save as New Policy'),
            'onclick' => 'OnBuyListingTemplateSwitcherObj.customSaveAsTemplate(\'' . $nick . '\');',
        ];
        $buttonBlock = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('save_custom_as_template', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        $isTemplateModeTemplate = (int)$this->isTemplateModeTemplate();

        $this->js->add(
            <<<JS
    require([
        'Switcher/Initialization',
        'OnBuy/Listing/Template/Switcher'
    ], function(){

        OnBuyListingTemplateSwitcherObj.updateEditVisibility('{$this->getTemplateNick()}');
        OnBuyListingTemplateSwitcherObj.updateButtonsVisibility('{$this->getTemplateNick()}');
        OnBuyListingTemplateSwitcherObj.updateTemplateLabelVisibility('{$this->getTemplateNick()}');

        $('{$this->getSwitcherId()}').observe('change', OnBuyListingTemplateSwitcherObj.change);

        if ({$isTemplateModeTemplate}) {
            $('{$this->getSwitcherId()}').simulate('change');
        }
    });
JS
        );

        return parent::_toHtml() .
            $this->getFormDataBlockHtml() .
            $this->getButtonsHtml();
    }

    //########################################
}
