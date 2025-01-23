<?php

namespace M2E\OnBuy\Model\Order;

use M2E\OnBuy\Model\ResourceModel\Order\Item as OrderItemResource;

class Item extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    private \M2E\OnBuy\Model\Order $order;
    private ?\M2E\OnBuy\Model\Magento\Product $magentoProduct = null;
    private ?\M2E\OnBuy\Model\Order\Item\ProxyObject $proxy = null;

    private \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory;

    // ----------------------------------------

    private ?\M2E\OnBuy\Model\Product $listingProduct = null;
    private \M2E\OnBuy\Model\Order\Item\ProxyObjectFactory $proxyObjectFactory;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\OnBuy\Model\Order\Item\OptionsFinder $optionsFinder;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;
    private \M2E\OnBuy\Model\Order\Item\ProductAssignService $productAssignService;
    /** @var \M2E\OnBuy\Model\Order\Repository */
    private Repository $orderRepository;
    private \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedProductRepository;

    public function __construct(
        \M2E\OnBuy\Model\Order\Repository $orderRepository,
        \M2E\OnBuy\Model\Order\Item\ProductAssignService $productAssignService,
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\Order\Item\OptionsFinder $optionsFinder,
        \M2E\OnBuy\Model\UnmanagedProduct\Repository $unmanagedProductRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\OnBuy\Model\Order\Item\ProxyObjectFactory $proxyObjectFactory,
        \M2E\OnBuy\Model\Magento\ProductFactory $magentoProductFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
        );
        $this->magentoProductFactory = $magentoProductFactory;
        $this->proxyObjectFactory = $proxyObjectFactory;
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->optionsFinder = $optionsFinder;
        $this->listingProductRepository = $listingProductRepository;
        $this->productAssignService = $productAssignService;
        $this->orderRepository = $orderRepository;
        $this->unmanagedProductRepository = $unmanagedProductRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(OrderItemResource::class);
    }

    // ----------------------------------------

    public function create(\M2E\OnBuy\Model\Order $order, string $channelProductId, string $productSku, int $qty): self
    {
        $this->setData(OrderItemResource::COLUMN_ORDER_ID, $order->getId())
             ->setData(OrderItemResource::COLUMN_CHANNEL_PRODUCT_ID, $channelProductId)
             ->setData(OrderItemResource::COLUMN_PRODUCT_SKU, $productSku)
             ->setData(OrderItemResource::COLUMN_QTY_PURCHASED, $qty);

        $this->initOrder($order);

        return $this;
    }

    public function getOrderId(): int
    {
        return (int)$this->getData(OrderItemResource::COLUMN_ORDER_ID);
    }

    public function getMagentoProductId(): ?int
    {
        $productId = $this->getData(OrderItemResource::COLUMN_MAGENTO_PRODUCT_ID);
        if ($productId === null) {
            return null;
        }

        return (int)$productId;
    }

    public function getQtyReserved(): int
    {
        return (int)$this->getData(OrderItemResource::COLUMN_QTY_RESERVED);
    }

    //region Column product_details
    public function setAssociatedOptions(array $options): self
    {
        $this->setSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'associated_options',
            $options
        );

        return $this;
    }

    public function getAssociatedOptions()
    {
        return $this->getSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'associated_options',
            []
        );
    }

    public function removeAssociatedOptions(): void
    {
        $this->setAssociatedOptions([]);
    }

    public function setAssociatedProducts(array $products): Item
    {
        $this->setSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'associated_products',
            $products
        );

        return $this;
    }

    public function getAssociatedProducts()
    {
        return $this->getSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'associated_products',
            []
        );
    }

    public function removeAssociatedProducts(): void
    {
        $this->setAssociatedProducts([]);
    }

    public function setReservedProducts(array $products): Item
    {
        $this->setSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'reserved_products',
            $products
        );

        return $this;
    }

    public function getReservedProducts()
    {
        return $this->getSetting(
            OrderItemResource::COLUMN_PRODUCT_DETAILS,
            'reserved_products',
            []
        );
    }
    //endregion

    //region Order
    public function setOrder(\M2E\OnBuy\Model\Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function initOrder(\M2E\OnBuy\Model\Order $order): void
    {
        $this->order = $order;
    }

    public function getOrder(): \M2E\OnBuy\Model\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->order)) {
            return $this->order;
        }

        return $this->order = $this->orderRepository->get($this->getOrderId());
    }
    //endregion

    //########################################

    public function setProduct($product): self
    {
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $this->magentoProduct = null;

            return $this;
        }

        if ($this->magentoProduct === null) {
            $this->magentoProduct = $this->magentoProductFactory->create();
        }
        $this->magentoProduct->setProduct($product);

        return $this;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getProduct(): ?\Magento\Catalog\Model\Product
    {
        if ($this->getMagentoProductId() === null) {
            return null;
        }

        if (!$this->isMagentoProductExists()) {
            return null;
        }

        return $this->getMagentoProduct()->getProduct();
    }

    public function getMagentoProduct(): ?\M2E\OnBuy\Model\Magento\Product
    {
        if ($this->getMagentoProductId() === null) {
            return null;
        }

        if ($this->magentoProduct === null) {
            $this->magentoProduct = $this->magentoProductFactory->createByProductId((int)$this->getMagentoProductId());
            $this->magentoProduct->setStoreId($this->getOrder()->getStoreId());
        }

        return $this->magentoProduct;
    }

    public function getStoreId(): int
    {
        $listingProduct = $this->getListingProduct();

        if ($listingProduct === null) {
            return $this->getOrder()->getStoreId();
        }

        $storeId = $listingProduct->getListing()->getStoreId();

        if ($storeId !== \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            return $storeId;
        }

        if ($this->getMagentoProductId() === null) {
            return $this->magentoStoreHelper->getDefaultStoreId();
        }

        $storeIds = $this
            ->magentoProductFactory
            ->create()
            ->setProductId($this->getMagentoProductId())
            ->getStoreIds();

        if (empty($storeIds)) {
            return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        return (int)array_shift($storeIds);
    }

    //########################################

    /**
     * Associate order item with product in magento
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \Exception
     */
    public function associateWithProduct(): void
    {
        if (
            $this->getMagentoProductId() === null
            || !$this->getMagentoProduct()->exists()
        ) {
            $this->productAssignService->assign(
                $this,
                $this->getAssociatedProduct(),
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION
            );
        }

        $supportedProductTypes = [
            \M2E\OnBuy\Helper\Magento\Product::TYPE_SIMPLE,
        ];

        if (!in_array($this->getMagentoProduct()->getTypeId(), $supportedProductTypes)) {
            $message = \M2E\OnBuy\Helper\Module\Log::encodeDescription(
                'Order Import does not support Product type: %type%.',
                [
                    'type' => $this->getMagentoProduct()->getTypeId(),
                ],
            );

            throw new \M2E\OnBuy\Model\Exception($message);
        }

        $this->associateVariationWithOptions();

        if (!$this->getMagentoProduct()->isStatusEnabled()) {
            throw new \M2E\OnBuy\Model\Exception('Product is disabled.');
        }
    }

    //########################################

    /**
     * Associate order item variation with options of magento product
     * @throws \LogicException
     * @throws \Exception
     */
    private function associateVariationWithOptions(): void
    {
        $magentoProduct = $this->getMagentoProduct();
        if ($magentoProduct === null) {
            return;
        }

        $existOptions = $this->getAssociatedOptions();
        $existProducts = $this->getAssociatedProducts();

        if (
            count($existProducts) == 1
            && ($magentoProduct->isDownloadableType()
                || $magentoProduct->isGroupedType()
                || $magentoProduct->isConfigurableType())
        ) {
            // grouped and configurable products can have only one associated product mapped with sold variation
            // so if count($existProducts) == 1 - there is no need for further actions
            return;
        }

        $productDetails = $this->getAssociatedProductDetails($magentoProduct);

        if (!isset($productDetails['associated_options'])) {
            return;
        }

        $existOptionsIds = array_keys($existOptions);
        $foundOptionsIds = array_keys($productDetails['associated_options']);

        if (empty($existOptions) && empty($existProducts)) {
            // options mapping invoked for the first time, use found options
            $this->setAssociatedOptions($productDetails['associated_options']);

            if (isset($productDetails['associated_products'])) {
                $this->setAssociatedProducts($productDetails['associated_products']);
            }

            $this->save();

            return;
        }

        if (!empty(array_diff($foundOptionsIds, $existOptionsIds))) {
            // options were already mapped, but not all of them
            throw new \M2E\OnBuy\Model\Exception\Logic('Selected Options do not match the Product Options.');
        }
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception
     */
    private function getAssociatedProductDetails(\M2E\OnBuy\Model\Magento\Product $magentoProduct): array
    {
        if (!$magentoProduct->getTypeId()) {
            return [];
        }

        $magentoOptions = $this
            ->prepareMagentoOptions($magentoProduct->getVariationInstance()->getVariationsTypeRaw());

        $optionsFinder = $this->optionsFinder;
        $optionsFinder->setProduct($magentoProduct)
                      ->setMagentoOptions($magentoOptions)
                      ->addChannelOptions();

        $optionsFinder->find();

        if (!$optionsFinder->hasFailedOptions()) {
            return $optionsFinder->getOptionsData();
        }

        throw new \M2E\OnBuy\Model\Exception($optionsFinder->getOptionsNotFoundMessage());
    }

    //########################################

    public function assignProduct($productId): void
    {
        $magentoProduct = $this->magentoProductFactory->createByProductId((int)$productId);

        if (!$magentoProduct->exists()) {
            $this->setData('product_id');
            $this->setAssociatedProducts([]);
            $this->setAssociatedOptions([]);
            $this->save();

            throw new \InvalidArgumentException('Product does not exist.');
        }

        $this->setMagentoProductId((int)$productId);

        $this->save();
    }

    public function setMagentoProductId(int $productId)
    {
        $this->setData(OrderItemResource::COLUMN_MAGENTO_PRODUCT_ID, $productId);
    }

    public function removeMagentoProductId(): void
    {
        $this->setData(OrderItemResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    //########################################

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function unassignProduct()
    {
        $this->setData('product_id');
        $this->setAssociatedProducts([]);
        $this->setAssociatedOptions([]);

        if ($this->getOrder()->getReserve()->isPlaced()) {
            $this->getOrder()->getReserve()->cancel();
            $this->getOrder()->getReserve()->addSuccessLogCancelQty();
        }

        $this->save();
    }

    //########################################

    public function pretendedToBeSimple(): bool
    {
        return false;
    }

    //########################################

    public function getAdditionalData(): array
    {
        $value = $this->getData('additional_data');
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }

    public function isMagentoProductExists(): bool
    {
        return $this->magentoProductFactory->createByProductId((int)$this->getMagentoProductId())->exists();
    }

    /**
     * @return \M2E\OnBuy\Model\Order\Item\ProxyObject
     */
    public function getProxy(): \M2E\OnBuy\Model\Order\Item\ProxyObject
    {
        if ($this->proxy === null) {
            $this->proxy = $this->proxyObjectFactory->create($this);
        }

        return $this->proxy;
    }

    // ----------------------------------------

    public function getAccount(): \M2E\OnBuy\Model\Account
    {
        return $this->getOrder()->getAccount();
    }

    // ----------------------------------------

    public function getListingProduct(): ?\M2E\OnBuy\Model\Product
    {
        if ($this->listingProduct === null) {
            $listingProduct = $this->listingProductRepository->findBySkuAndSiteId(
                $this->getProductSku(),
                $this->getSiteId()
            );

            $this->listingProduct = $listingProduct;
        }

        return $this->listingProduct;
    }

    // ----------------------------------------

    public function getChannelProductId(): string
    {
        return $this->getData(OrderItemResource::COLUMN_CHANNEL_PRODUCT_ID);
    }

    public function setChannelProductTitle(string $channelProductTitle): self
    {
        $this->setData(OrderItemResource::COLUMN_PRODUCT_TITLE, $channelProductTitle);

        return $this;
    }

    public function getChannelProductTitle(): string
    {
        return $this->getData(OrderItemResource::COLUMN_PRODUCT_TITLE);
    }

    public function getProductSku(): string
    {
        return $this->getData(OrderItemResource::COLUMN_PRODUCT_SKU);
    }

    public function setSalePrice(float $price): self
    {
        $this->setData(OrderItemResource::COLUMN_SALE_PRICE, $price);

        return $this;
    }

    public function getSalePrice(): float
    {
        return (float)$this->getData(OrderItemResource::COLUMN_SALE_PRICE);
    }

    public function getQtyPurchased(): int
    {
        return (int)$this->getData(OrderItemResource::COLUMN_QTY_PURCHASED);
    }

    public function getQtyDispatched(): int
    {
        return (int)$this->getData(OrderItemResource::COLUMN_QTY_DISPATCHED);
    }

    public function setQtyDispatched(int $qty): self
    {
        $this->setData(OrderItemResource::COLUMN_QTY_DISPATCHED, $qty);

        return $this;
    }

    public function setQtyReserved(int $qty): self
    {
        $this->setData(OrderItemResource::COLUMN_QTY_RESERVED, $qty);

        return $this;
    }

    // ---------------------------------------

    public function setTaxDetails(array $details): self
    {
        $this->setData(OrderItemResource::COLUMN_TAX_DETAILS, json_encode($details));

        return $this;
    }

    public function getTaxDetails(): array
    {
        $taxDetails = $this->getData(OrderItemResource::COLUMN_TAX_DETAILS);
        if (empty($taxDetails)) {
            return [];
        }

        return json_decode($taxDetails, true) ?? [];
    }

    public function hasTax(): bool
    {
        $taxDetails = $this->getTaxDetails();

        return !empty($taxDetails['rate']);
    }

    /**
     * @return float
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();

        return (float)($taxDetails['amount'] ?? 0.0);
    }

    /**
     * @return int|float
     */
    public function getTaxRate()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return $taxDetails['rate'];
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasVariation(): bool
    {
        return false;
    }

    public function setTrackingDetails(?array $details): self
    {
        $this->setData(OrderItemResource::COLUMN_TRACKING_DETAILS, json_encode($details ?? []));

        return $this;
    }

    public function getTrackingDetails(): array
    {
        $trackingDetails = $this->getData(OrderItemResource::COLUMN_TRACKING_DETAILS);
        if (empty($trackingDetails)) {
            return [];
        }

        return json_decode($trackingDetails, true);
    }

    public function getExpectedDispatchDate(): \DateTimeImmutable
    {
        $date = $this->getData(OrderItemResource::COLUMN_EXPECTED_DISPATCH_DATE);

        return \M2E\Core\Helper\Date::createImmutableDateGmt($date);
    }

    public function setExpectedDispatchDate(\DateTimeInterface $date): self
    {
        $this->setData(OrderItemResource::COLUMN_EXPECTED_DISPATCH_DATE, $date->format('Y-m-d H:i:s'));

        return $this;
    }

    public function getFee(): array
    {
        $fee = $this->getData(OrderItemResource::COLUMN_FEE);
        if (empty($fee)) {
            return [];
        }

        return json_decode($fee, true);
    }

    public function setFee(array $fee): self
    {
        $this->setData(OrderItemResource::COLUMN_FEE, json_encode($fee));

        return $this;
    }

    public function canCreateMagentoOrder(): bool
    {
        return $this->isOrdersCreationEnabled();
    }

    public function isReservable(): bool
    {
        return $this->isOrdersCreationEnabled();
    }

    protected function isOrdersCreationEnabled(): bool
    {
        $listingProduct = $this->getListingProduct();

        if ($listingProduct === null) {
            return $this->getAccount()->getOrdersSettings()->isUnmanagedListingEnabled();
        }

        return $this->getAccount()->getOrdersSettings()->isListingEnabled();
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     * @throws \M2E\OnBuy\Model\Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function getAssociatedProduct(): \Magento\Catalog\Model\Product
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getListingProduct() !== null) {
            return $this->getListingProduct()->getMagentoProduct()->getProduct();
        }

        // Unmanaged Item
        // ---------------------------------------
        $sku = $this->getProductSku();

        if (
            $sku != ''
            && strlen($sku) <= \M2E\OnBuy\Helper\Magento\Product::SKU_MAX_LENGTH
        ) {
            $collection = $this->magentoProductCollectionFactory->create();
            $collection->setStoreId($this->getOrder()->getAssociatedStoreId());
            $collection->addAttributeToSelect('sku');
            $collection->addAttributeToFilter('sku', $sku);

            /** @var \Magento\Catalog\Model\Product $foundedProduct */
            $foundedProduct = $collection->getFirstItem();

            if (!$foundedProduct->isObjectNew()) {
                $this->associateWithProductEvent($foundedProduct);

                return $foundedProduct;
            }

            // Unmanaged Item and linked
            // ---------------------------------------
            $unmanagedProduct = $this->unmanagedProductRepository->findBySkuAndSite($sku, $this->getSiteId());

            if ($unmanagedProduct !== null && $unmanagedProduct->getMagentoProductId() !== 0) {
                return $unmanagedProduct->getMagentoProduct()->getProduct();
            }
        }

        // Create new Product in Magento
        // ---------------------------------------
        $newProduct = $this->createProduct();
        $this->associateWithProductEvent($newProduct);

        return $newProduct;
    }

    public function prepareMagentoOptions($options): array
    {
        return \M2E\OnBuy\Helper\Component\OnBuy::prepareOptionsForOrders($options);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \M2E\OnBuy\Model\Order\Exception\ProductCreationDisabled
     */
    protected function createProduct(): \Magento\Catalog\Model\Product
    {
        throw new \M2E\OnBuy\Model\Order\Exception\ProductCreationDisabled(
            (string)__('The product associated with this order could not be found in the Magento catalog.'),
        );
    }

    protected function associateWithProductEvent(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->hasVariation()) {
            $this->_eventManager->dispatch('m2e_onbuy_associate_order_item_to_product', [
                'product' => $product,
                'order_item' => $this,
            ]);
        }
    }

    public function setOriginalPrice(float $price): self
    {
        $this->setData(OrderItemResource::COLUMN_ORIGINAL_PRICE, $price);

        return $this;
    }

    public function getOriginalPrice(): float
    {
        return (float)$this->getData(OrderItemResource::COLUMN_ORIGINAL_PRICE);
    }

    public function setChannelSku(string $channelSku): self
    {
        $this->setData(OrderItemResource::COLUMN_PRODUCT_SKU, $channelSku);

        return $this;
    }

    public function getChannelSku(): string
    {
        return (string)$this->getData(OrderItemResource::COLUMN_PRODUCT_SKU);
    }

    public function getSiteId(): int
    {
        return $this->getOrder()->getSiteId();
    }
}
