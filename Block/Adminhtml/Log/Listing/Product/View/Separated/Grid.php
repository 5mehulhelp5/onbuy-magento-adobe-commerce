<?php

namespace M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\View\Separated;

use M2E\OnBuy\Block\Adminhtml\Log\Listing\View;

class Grid extends \M2E\OnBuy\Block\Adminhtml\Log\Listing\Product\AbstractGrid
{
    private \M2E\OnBuy\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory;

    public function __construct(
        \M2E\OnBuy\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Account $accountResource,
        \M2E\OnBuy\Model\Config\Manager $config,
        \M2E\OnBuy\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\OnBuy\Helper\View $viewHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\OnBuy\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct(
            $accountResource,
            $config,
            $wrapperCollectionFactory,
            $customCollectionFactory,
            $resourceConnection,
            $viewHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $data,
        );
        $this->listingLogCollectionFactory = $listingLogCollectionFactory;
    }

    protected function getViewMode()
    {
        return View\Switcher::VIEW_MODE_SEPARATED;
    }

    protected function _prepareCollection()
    {
        $collection = $this->listingLogCollectionFactory->create();

        $this->applyFilters($collection);

        $isNeedCombine = $this->isNeedCombineMessages();

        if ($isNeedCombine) {
            $collection->getSelect()->columns(
                ['main_table.create_date' => new \Zend_Db_Expr('MAX(main_table.create_date)')]
            );
            $collection->getSelect()->group(['main_table.listing_product_id', 'main_table.description']);
        }

        $this->setCollection($collection);

        $result = parent::_prepareCollection();

        if ($isNeedCombine) {
            $this->prepareMessageCount($collection);
        }

        return $result;
    }

    protected function getExcludedActionTitles(): array
    {
        return [
            \M2E\OnBuy\Model\Listing\Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            \M2E\OnBuy\Model\Listing\Log::ACTION_DELETE_PRODUCT => '',
            \M2E\OnBuy\Model\Listing\Log::ACTION_SWITCH_TO_AFN => '',
            \M2E\OnBuy\Model\Listing\Log::ACTION_SWITCH_TO_MFN => '',
            \M2E\OnBuy\Model\Listing\Log::ACTION_CHANGE_PRODUCT_TIER_PRICE => '',
            \M2E\OnBuy\Model\Listing\Log::ACTION_RESET_BLOCKED_PRODUCT => '',
        ];
    }
}
