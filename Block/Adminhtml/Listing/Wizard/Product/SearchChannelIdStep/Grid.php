<?php

declare(strict_types=1);

namespace M2E\OnBuy\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep;

use M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Grid extends \M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\OnBuy\Model\Listing $listing;
    private \M2E\OnBuy\Model\Listing\Wizard\Manager $wizardManager;
    private \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    private \M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product $wizardProductResource;
    private \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory;
    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    private \M2E\OnBuy\Model\Settings $settings;

    public function __construct(
        \M2E\OnBuy\Model\Listing $listing,
        \M2E\OnBuy\Model\Listing\Wizard\Manager $wizardManager,
        \M2E\Core\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\OnBuy\Model\ResourceModel\Listing\Wizard\Product $wizardProductResource,
        \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\OnBuy\Model\Settings $settings,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->listing = $listing;
        $this->wizardManager = $wizardManager;
        $this->wizardProductResource = $wizardProductResource;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->settings = $settings;
        $this->productRepository = $productRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('SearchOPCForListingProductsGrid' . $this->listing->getId());
    }

    protected function _prepareCollection()
    {
        $listingProductsIds = $this->wizardManager->getProductsIds();

        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $wizardProductTableName = $this->wizardProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $wizardProductTableName],
            sprintf('%s = entity_id', WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => WizardProductResource::COLUMN_ID,
                'status_search' => WizardProductResource::COLUMN_CHANNEL_PRODUCT_ID_SEARCH_STATUS,
                'opc' => WizardProductResource::COLUMN_CHANNEL_PRODUCT_ID,
            ],
            '{{table}}.wizard_id=' . $this->wizardManager->getWizardId(),
        );

        $collection->getSelect()->where('lp.magento_product_id IN (?)', $listingProductsIds);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->listing->getStoreId(),
            'renderer' => \M2E\OnBuy\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Product Title / Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('opc', [
            'header' => __('OPC'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'opc',
            'escape' => false,
            'filter_index' => 'opc',
            'frame_callback' => [$this, 'callbackColumnOnBuyProductId'],
        ]);

        $this->addColumn('settings', [
            'header' => __('Search Values'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'type' => 'text',
            'index' => 'id',
            'frame_callback' => [$this, 'callbackColumnSearchValues'],
        ]);

        $this->addColumn('status_search', [
            'header' => __('Search Status'),
            'index' => 'status_search',
            'filter_index' => 'status_search',
            'sortable' => false,
            'type' => 'options',
            'options' => [
                \M2E\OnBuy\Model\Product::SEARCH_STATUS_NONE => __('None'),
                \M2E\OnBuy\Model\Product::SEARCH_STATUS_COMPLETED => __('Completed'),
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = $this->_escaper->escapeHtml($productTitle);

        $value = '<span>' . $productTitle . '</span>';

        $tempSku = $row->getData('sku');
        if ($tempSku === null) {
            $tempSku = $this->magentoProductFactory->create()
                                                   ->setProductId($row->getData('entity_id'))
                                                   ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') .
            ':</strong> ' . $this->_escaper->escapeHtml($tempSku) . '<br/>';

        return $value;
    }

    public function callbackColumnOnBuyProductId($opc, $row, $column, $isExport)
    {
        $isSearchCompleted = ((int)$row['status_search']) === \M2E\OnBuy\Model\Product::SEARCH_STATUS_COMPLETED;
        if ($isSearchCompleted) {
            if (empty($opc)) {
                return __('Not Found');
            }

            return $row->getData('opc');
        }

        if (empty($opc)) {
            return __('Searching...');
        }

        return $row->getData('opc');
    }

    public function callbackColumnSearchValues($value, $row, $column, $isExport)
    {
        $eanAttributeCode = $this->settings->getIdentifierCodeValue();
        if (!$eanAttributeCode) {
            return __('Not Set');
        }

        $storeId = (int)$row['store_id'];
        $magentoProduct = $this->productRepository->get($row->getData('sku'), false, $storeId);
        $eanAttributeCode = $this->settings->getIdentifierCodeValue();
        $searchValue = $magentoProduct->getCustomAttribute($eanAttributeCode);
        if ($searchValue) {
            $searchValue = $this->_escaper->escapeHtml($searchValue->getValue());
        } else {
            $searchValue = __('Not Set');
        }

        return '<strong>' . __('EAN') . ':</strong>' . ' ' . $searchValue;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $html = '';
        switch ($row->getData('status_search')) {
            case \M2E\OnBuy\Model\Product::SEARCH_STATUS_NONE:
                $html .= '<span style="color: gray;">' . __('None') . '</span>';
                break;

            case \M2E\OnBuy\Model\Product::SEARCH_STATUS_COMPLETED:
                $html .= '<span style="color: green;">' . __('Completed') . '</span>';

                break;
        }

        return $html;
    }

    protected function callbackFilterTitle($collection, $column): void
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }
}
