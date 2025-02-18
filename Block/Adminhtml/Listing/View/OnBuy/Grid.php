<?php

namespace M2E\OnBuy\Block\Adminhtml\Listing\View\OnBuy;

use M2E\OnBuy\Block\Adminhtml\Log\AbstractGrid;
use M2E\OnBuy\Model\Product;
use M2E\OnBuy\Model\ResourceModel\Product as ListingProductResource;

class Grid extends \M2E\OnBuy\Block\Adminhtml\Listing\View\AbstractGrid
{
    private \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\OnBuy\Helper\Data\Session $sessionDataHelper;
    private \M2E\OnBuy\Model\Currency $currency;
    private ListingProductResource $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\OnBuy\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $productRepository,
        ListingProductResource $listingProductResource,
        \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\OnBuy\Helper\Data\Session $sessionDataHelper,
        \M2E\OnBuy\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\OnBuy\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\OnBuy\Helper\Data\GlobalData $globalDataHelper,
        \M2E\OnBuy\Model\Currency $currency,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->currency = $currency;
        $this->listingProductResource = $listingProductResource;
        $this->urlHelper = $urlHelper;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        parent::__construct(
            $uiListingRuntimeStorage,
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionDataHelper,
            $data
        );
        $this->productRepository = $productRepository;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setDefaultSort(false);

        $this->setId('onbuyListingViewGrid' . $this->getListing()->getId());

        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if (!$collection) {
            return $this;
        }

        $columnIndex = $column->getFilterIndex() ?: $column->getIndex();

        $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()));

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setItemObjectClass(Row::class);
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->getListing()->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            ['lp' => $this->listingProductResource->getMainTable()],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                Row::KEY_LISTING_PRODUCT_ID => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'channel_product_id' => ListingProductResource::COLUMN_CHANNEL_PRODUCT_ID,
                'online_sku' => ListingProductResource::COLUMN_ONLINE_SKU,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'opc' => ListingProductResource::COLUMN_OPC,
                'online_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'online_product_url' => ListingProductResource::COLUMN_ONLINE_PRODUCT_URL,
                'listing_id' => ListingProductResource::COLUMN_LISTING_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->getListing()->getId()
            )
        );

        $collection->getSelect()->group('lp.id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvListingGrid', __('CSV'));

        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->getListing()->getStoreId(),
            'renderer' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Product Title / Product SKU'),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'online_title',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);
        $this->addColumn('product_opc', [
            'header' => __('OPC'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'opc',
            'renderer' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Renderer\Opc::class,
        ]);
        $this->addColumn(
            'online_qty',
            [
                'header' => __('Available QTY'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'index' => 'online_qty',
                'sortable' => true,
                'filter_index' => 'online_qty',
                'renderer' => \M2E\OnBuy\Block\Adminhtml\Grid\Column\Renderer\OnlineQty::class,
            ]
        );

        $priceColumn = [
            'header' => __('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_price',
            'sortable' => true,
            'frame_callback' => [$this, 'callbackColumnPrice'],
        ];

        $this->addColumn('price', $priceColumn);

        $statusColumn = [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
                Product::STATUS_BLOCKED => Product::getStatusTitle(Product::STATUS_BLOCKED),
            ],
            'showLogIcon' => true,
            'renderer' => \M2E\OnBuy\Block\Adminhtml\Grid\Column\Renderer\Status::class,
            'filter_condition_callback' => [$this, 'callbackFilterStatus'],
        ];

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField(Row::KEY_LISTING_PRODUCT_ID);
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Configure groups
        // ---------------------------------------

        $groups = [
            'actions' => __('Listing Actions'),
            'other' => __('Other'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------

        $this->getMassactionBlock()->addItem('list', [
            'label' => __('List Item(s) on OnBuy'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('revise', [
            'label' => __('Revise Item(s) on ' . \M2E\OnBuy\Helper\Module::getChannelTitle()),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('relist', [
            'label' => __('Relist Item(s) on ' . \M2E\OnBuy\Helper\Module::getChannelTitle()),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stop', [
            'label' => __('Stop Item(s) on ' . \M2E\OnBuy\Helper\Module::getChannelTitle()),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', [
            'label' => __(
                'Remove from %channel_title / Remove from Listing',
                [
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            ),
            'url' => '',
        ], 'actions');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _afterLoadCollection()
    {
        /** @var Row[] $items */
        $items = $this->getCollection()->getItems();

        $listingProductIds = [];
        foreach ($items as $item) {
            $listingProductIds[] = $item->getListingProductId();
        }

        $products = $this->productRepository->findByIds($listingProductIds);

        $sortedProductsById = [];
        foreach ($products as $product) {
            $sortedProductsById[$product->getId()] = $product;
        }

        foreach ($items as $item) {
            $item->setListingProduct($sortedProductsById[$item->getListingProductId()] ?? null);
        }

        return parent::_afterLoadCollection();
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();

        $onlineTitle = $row->getData('online_title');
        if (!empty($onlineTitle)) {
            $title = $onlineTitle;
        }

        $title = \M2E\Core\Helper\Data::escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');

        if ($row->getData('sku') === null) {
            $sku = $this->ourMagentoProductFactory->create()
                                                  ->setProductId($row->getData('entity_id'))
                                                  ->getSku();
        }

        if ($isExport) {
            return \M2E\Core\Helper\Data::escapeHtml($sku);
        }

        $valueHtml .= '<br/>' .
            '<strong>' . __('SKU') . ':</strong>&nbsp;' .
            \M2E\Core\Helper\Data::escapeHtml($sku);

        return $valueHtml;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
                ['attribute' => 'online_title', 'like' => '%' . $value . '%'],
                ['attribute' => 'online_sku', 'like' => '%' . $value . '%'],
            ]
        );
    }

    /**
     * @param $value
     * @param Row $row
     * @param $column
     * @param $isExport
     *
     * @return mixed|string
     */
    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return (string)$value;
        }

        $productStatus = $row->getData('status');

        if ((int)$productStatus === \M2E\OnBuy\Model\Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray;">%s</span>',
                __('Not Listed')
            );
        }

        return $this->currency->formatPrice(
            $this->getListing()->getSite()->getCurrencyCode(),
            (float)$value
        );
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null) {
            return;
        }

        if (is_array($value) && isset($value['value'])) {
            $collection->addFieldToFilter($index, (int)$value['value']);
        } else {
            if (!is_array($value) && $value !== null) {
                $collection->addFieldToFilter($index, (int)$value);
            }
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/listing/view', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getTooltipHtml(string $content, $id = false): string
    {
        return <<<HTML
<div id="$id" class="OnBuy-field-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content" style="">
        {$content}
    </div>
</div>
HTML;
    }

    protected function _beforeToHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add("OnBuyListingViewOnBuyGridObj.afterInitPage()");

            return parent::_beforeToHtml();
        }

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $this->getId();
        $ignoreListings = \M2E\Core\Helper\Json::encode([$this->getListing()->getId()]);

        $this->jsUrl->addUrls([
            'runListProducts' => $this->getUrl('*/listing/runListProducts'),
            'runRelistProducts' => $this->getUrl('*/listing/runRelistProducts'),
            'runReviseProducts' => $this->getUrl('*/listing/runReviseProducts'),
            'runStopProducts' => $this->getUrl('*/listing/runStopProducts'),
            'runStopAndRemoveProducts' => $this->getUrl('*/listing/runStopAndRemoveProducts'),
            'previewItems' => $this->getUrl('*/listing/previewItems'),
        ]);

        $this->jsUrl->add(
            $this->getUrl('*/log_listing_product/index'),
            'log_listing_product/index'
        );

        $this->jsUrl->add(
            $this->getUrl('*/log_listing_product/index', [
                AbstractGrid::LISTING_ID_FIELD => $this->getListing()->getId(),
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/listing/view',
                    ['id' => $this->getListing()->getId()]
                ),
            ]),
            'logViewUrl'
        );
        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add(
            $this->getUrl('*/listing_moving/moveToListingGrid'),
            'listing_moving/moveToListingGrid'
        );

        $taskCompletedWarningMessage = __('"%task_title%" task has completed with warnings. ' .
            '<a target="_blank" href="%url%">View Log</a> for details.');

        $taskCompletedErrorMessage = __('"%task_title%" task has completed with errors. ' .
            '<a target="_blank" href="%url%">View Log</a> for details.');

        $channelTitle = \M2E\OnBuy\Helper\Module::getChannelTitle();

        $this->jsTranslator->addTranslations([
            'task_completed_message' => __('Task completed. Please wait ...'),
            'task_completed_success_message' => __('"%task_title%" task has completed.'),
            'task_completed_warning_message' => $taskCompletedWarningMessage,
            'task_completed_error_message' => $taskCompletedErrorMessage,
            'sending_data_message' => __(
                'Sending %product_title% Product(s) data on %channel_title.',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'view_full_product_log' => __('View Full Product Log.'),
            'listing_selected_items_message' => __(
                'Listing Selected Items On %channel_title',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'revising_selected_items_message' => __(
                'Revising Selected Items On %channel_title',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'relisting_selected_items_message' => __(
                'Relisting Selected Items On %channel_title',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'stopping_selected_items_message' => __(
                'Stopping Selected Items On %channel_title',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'stopping_and_removing_selected_items_message' => __(
                'Removing from %channel_title And Removing From Listing Selected Items',
                [
                    'channel_title' => $channelTitle,
                ]
            ),
            'removing_selected_items_message' => __('Removing From Listing Selected Items'),

            'Please select the Products you want to perform the Action on.' =>
                __('Please select the Products you want to perform the Action on.'),
            'Please select Action.' => __('Please select Action.'),
            'Specifics' => __('Specifics'),
        ]);

        $this->js->add(
            <<<JS
    OnBuy.productsIdsForList = '$productsIdsForList';
    OnBuy.customData.gridId = '$gridId';
    OnBuy.customData.ignoreListings = '$ignoreListings';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'OnBuy/Listing/View/OnBuy/Grid',
        'OnBuy/Listing/VariationProductManage'
    ], function() {
        window.OnBuyListingVariationProductManageObj = new OnBuyListingVariationProductManage()
        window.OnBuyListingViewOnBuyGridObj = new OnBuyListingViewOnBuyGrid('$gridId', {$this->getListing()->getId()});

        OnBuyListingViewOnBuyGridObj.afterInitPage();

        OnBuyListingViewOnBuyGridObj.actionHandler.setProgressBar('listing_view_progress_bar');
        OnBuyListingViewOnBuyGridObj.actionHandler.setGridWrapper('listing_view_content_container');

        if (OnBuy.productsIdsForList) {
            OnBuyListingViewOnBuyGridObj.getGridMassActionObj().checkedString = OnBuy.productsIdsForList;
            OnBuyListingViewOnBuyGridObj.actionHandler.listAction();
        }
    });
JS
        );

        return parent::_beforeToHtml();
    }
}
