<?php

namespace M2E\OnBuy\Block\Adminhtml\Order\View;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractGrid;

class Item extends AbstractGrid
{
    protected \Magento\Catalog\Model\Product $productModel;
    protected \Magento\Tax\Model\Calculation $taxCalculator;
    private \M2E\OnBuy\Model\Order $order;
    private \M2E\OnBuy\Model\Currency $currency;
    private \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Item\Repository $orderItemRepository,
        \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\OnBuy\Model\Currency $currency,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\OnBuy\Model\Order $order,
        array $data = []
    ) {
        $this->productModel = $productModel;
        $this->taxCalculator = $taxCalculator;
        $this->order = $order;
        $this->currency = $currency;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('onbuyOrderViewItem');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        $this->_defaultLimit = 200;
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderItemRepository->getOrderItemCollection($this->order->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('products', [
            'header' => __('Product'),
            'align' => 'left',
            'width' => '*',
            'index' => 'product_id',
            'frame_callback' => [$this, 'callbackColumnProduct'],
        ]);

        $this->addColumn('stock_availability', [
            'header' => __('Stock Availability'),
            'width' => '100px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnIsInStock'],
        ]);

        $this->addColumn('original_price', [
            'header' => __('Original Price'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnOriginalPrice'],
        ]);

        $this->addColumn('sale_price', [
            'header' => __('On Buy Price'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'index' => 'sale_price',
            'frame_callback' => [$this, 'callbackColumnOnBuyPrice'],
        ]);

        $this->addColumn('qty_purchased', [
            'header' => __('QTY'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'index' => 'qty_purchased',
        ]);

        $this->addColumn('tax_percent', [
            'header' => __('Tax Percent'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnTaxPercent'],
        ]);

        $this->addColumn('tax_amount', [
            'header' => __('Tax Amount'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnTaxAmount'],
        ]);

        $this->addColumn('row_total', [
            'header' => __('Row Total'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnRowTotal'],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string $value
     * @param \M2E\OnBuy\Model\Order\Item $row
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @param bool $isExport
     *
     * @return string
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function callbackColumnProduct($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        $productLink = '';
        if ($row->getMagentoProductId()) {
            $productUrl = $this->getUrl('catalog/product/edit', [
                'id' => $row->getMagentoProductId(),
                'store' => $row->getOrder()->getStoreId(),
            ]);
            $productLink = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $productUrl,
                __('View')
            );
        }

        $editLink = '';
        if (!$row->getMagentoProductId()) {
            $onclick = sprintf(
                "OrderEditItemObj.edit('%s', '%s')",
                $this->getId(),
                $row->getId()
            );
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Link to Magento Product')
            );
        }

        if ($row->getMagentoProductId() && $row->getMagentoProduct()->isProductWithVariations()) {
            $onclick = sprintf(
                "OrderEditItemObj.edit('%s', '%s')",
                $this->getId(),
                $row->getId()
            );
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Set Options')
            );
            $editLink .= '&nbsp;|&nbsp;';
        }

        $discardLink = '';
        if ($row->getMagentoProductId()) {
            $onclick = sprintf(
                "OrderEditItemObj.unassignProduct('%s', '%s')",
                $this->getId(),
                $row->getId()
            );
            $discardLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Unlink')
            );
        }

        $titleLine = sprintf(
            '<p><strong>%s</strong></p>',
            \M2E\Core\Helper\Data::escapeHtml($row->getChannelProductTitle())
        );
        $skuLine = sprintf(
            '<p><strong>%s:</strong> %s</p>',
            __('SKU'),
            \M2E\Core\Helper\Data::escapeHtml($row->getProductSku())
        );
        $actionLine = sprintf(
            '<div style="float: left;">%s</div><div style="float: right;">%s%s</div>',
            $productLink,
            $editLink,
            $discardLink
        );

        return $titleLine . $skuLine . $actionLine;
    }

    public function callbackColumnIsInStock($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        if (!$row->isMagentoProductExists()) {
            return '<span style="color: red;">' . __('Product Not Found') . '</span>';
        }

        if ($row->getMagentoProduct() === null) {
            return __('N/A');
        }

        if (!$row->getMagentoProduct()->isStockAvailability()) {
            return '<span style="color: red;">' . __('Out Of Stock') . '</span>';
        }

        return __('In Stock');
    }

    public function callbackColumnOnBuyPrice($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            (float)$value
        );
    }

    public function callbackColumnOriginalPrice($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        $formattedPrice = __('N/A');

        $product = $row->getProduct();

        if ($product) {
            $magentoProduct = $this->ourMagentoProductFactory->create();
            $magentoProduct->setProduct($product);

            if ($magentoProduct->isGroupedType()) {
                $associatedProducts = $row->getAssociatedProducts();
                $price = $this->productModel
                    ->load(array_shift($associatedProducts))
                    ->getPrice();

                $formattedPrice = $this->order->getStore()->getCurrentCurrency()->format($price);
            } else {
                $formattedPrice = $this->order->getStore()
                                              ->getCurrentCurrency()
                                              ->format($row->getProduct()->getPrice());
            }
        }

        return $formattedPrice;
    }

    public function callbackColumnPrice($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            $row->getSalePrice()
        );
    }

    public function callbackColumnTaxPercent($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        $taxDetails = $row->getTaxDetails();
        if ($taxDetails === []) {
            return '0%';
        }

        $taxTotal = $taxDetails['rate'];
        if ($taxTotal === null) {
            return '0%';
        }

        return sprintf('%s%%', $taxTotal);
    }

    public function callbackColumnTaxAmount($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        $taxDetails = $row->getTaxDetails();
        if ($taxDetails === []) {
            return '0';
        }

        $taxTotal = $taxDetails['amount'];
        if ($taxTotal === null) {
            return '0';
        }

        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            $taxTotal
        );
    }

    public function callbackColumnRowTotal($value, \M2E\OnBuy\Model\Order\Item $row, $column, $isExport)
    {
        $total = $row->getQtyPurchased() * $row->getSalePrice();

        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            $total
        );
    }

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/order/orderItemGrid', ['_current' => true]);
    }
}
