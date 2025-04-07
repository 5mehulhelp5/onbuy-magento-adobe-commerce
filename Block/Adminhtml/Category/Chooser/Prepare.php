<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Category\Chooser;

class Prepare extends \M2E\OnBuy\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('category/chooser/prepare.phtml');
        $urlBuilder = $this->_urlBuilder;
        $this->jsUrl->addUrls(
            [
                'category/getCategoryAttributesHtml' => $urlBuilder->getUrl(
                    '*/category/getCategoryAttributesHtml'
                ),
                'category/getChildCategories' => $urlBuilder->getUrl(
                    '*/category/getChildCategories'
                ),
                'category/getChooserEditHtml' => $urlBuilder->getUrl(
                    '*/category/getChooserEditHtml'
                ),
                'category/getCountsOfAttributes' => $urlBuilder->getUrl(
                    '*/category/getCountsOfAttributes'
                ),
                'category/getEditedCategoryInfo' => $urlBuilder->getUrl(
                    '*/category/getEditedCategoryInfo'
                ),
                'category/getRecent' => $urlBuilder->getUrl(
                    '*/category/getRecent'
                ),
                'category/getSelectedCategoryDetails' => $urlBuilder->getUrl(
                    '*/category/getSelectedCategoryDetails'
                ),
                'category/saveCategoryAttributes' => $urlBuilder->getUrl(
                    '*/category/saveCategoryAttributes'
                ),
                'category/saveCategoryAttributesAjax' => $urlBuilder->getUrl(
                    '*/category/saveCategoryAttributesAjax'
                ),
            ],
        );

        $this->jsTranslator->addTranslations([
            'Select' => __('Select'),
            'Reset' => __('Reset'),
            'No recently used Categories' => __('No recently used Categories'),
            'Change Category' => __('Change Category'),
            'Edit' => __('Edit'),
            'Category' => __('Category'),
            'Not Selected' => __('Not Selected'),
            'No results' => __('No results'),
            'No saved Categories' => __('No saved Categories'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
        ]);
    }

    public function getSearchUrl(): string
    {
        return $this->getUrl('*/category/search');
    }

    public function getAccountId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getAccountId();
    }

    public function getSiteId(): ?int
    {
        return $this->uiListingRuntimeStorage->getListing()->getSite()->getId();
    }
}
