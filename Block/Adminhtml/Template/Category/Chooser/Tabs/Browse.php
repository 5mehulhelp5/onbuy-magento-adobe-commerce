<?php

namespace M2E\OnBuy\Block\Adminhtml\Template\Category\Chooser\Tabs;

class Browse extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    public \M2E\OnBuy\Helper\View\OnBuy $viewHelper;
    private \M2E\OnBuy\Helper\Module\Wizard $wizardHelper;

    public function __construct(
        \M2E\OnBuy\Helper\View\OnBuy $viewHelper,
        \M2E\OnBuy\Helper\Module\Wizard $wizardHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->wizardHelper = $wizardHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('onBuyCategoryChooserCategoryBrowse');
        $this->setTemplate('template/category/chooser/tabs/browse.phtml');
    }

    public function isWizardActive()
    {
        return $this->wizardHelper->isActive(\M2E\OnBuy\Helper\View\OnBuy::WIZARD_INSTALLATION_NICK);
    }
}
