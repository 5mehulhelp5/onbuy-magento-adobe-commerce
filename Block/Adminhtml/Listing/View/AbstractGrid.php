<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\View;

abstract class AbstractGrid extends \M2E\OnBuy\Block\Adminhtml\Magento\Product\Grid
{
    private \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\OnBuy\Helper\Data $dataHelper,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        parent::__construct($globalDataHelper, $sessionHelper, $context, $backendHelper, $dataHelper, $data);
    }

    public function setCollection($collection)
    {
        $collection->setStoreId($this->getListing()->getStoreId());

        parent::setCollection($collection);
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/view/grid.css');

        return parent::_prepareLayout();
    }

    public function getStoreId(): int
    {
        return $this->getListing()->getStoreId();
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'Are you sure you want to create empty Listing?' => \M2E\Core\Helper\Data::escapeJs(
                (string)__('Are you sure you want to create empty Listing?')
            ),
        ]);

        // ---------------------------------------

        return parent::_toHtml();
    }

    protected function getListing(): \M2E\OnBuy\Model\Listing
    {
        return $this->uiListingRuntimeStorage->getListing();
    }
}
