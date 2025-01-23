<?php

namespace M2E\OnBuy\Observer\Product\AddUpdate;

class Before extends AbstractAddUpdate
{
    public const BEFORE_EVENT_KEY = 'm2e_onbuy_before_event_key';

    private \M2E\OnBuy\Observer\Product\AddUpdate\Before\ProxyFactory $proxyFactory;
    private \M2E\OnBuy\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private ?\M2E\OnBuy\Observer\Product\AddUpdate\Before\Proxy $proxy = null;
    public static array $proxyStorage = [];
    private \M2E\OnBuy\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory,
        \M2E\OnBuy\Helper\Magento\Product $magentoProductHelper,
        \M2E\OnBuy\Observer\Product\AddUpdate\Before\ProxyFactory $proxyFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\OnBuy\Model\Magento\ProductFactory $ourMagentoProductFactory
    ) {
        parent::__construct(
            $listingProductRepository,
            $productFactory,
            $ourMagentoProductFactory
        );

        $this->proxyFactory = $proxyFactory;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();
        $this->clearStoredProxy();
    }

    public function afterProcess(): void
    {
        parent::afterProcess();
        $this->storeProxy();
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    protected function process(): void
    {
        if ($this->isAddingProductProcess()) {
            return;
        }

        $this->reloadProduct();

        $this->getProxy()->setData('name', $this->getProduct()->getName());

        $this->getProxy()->setWebsiteIds($this->getProduct()->getWebsiteIds());
        $this->getProxy()->setCategoriesIds($this->getProduct()->getCategoryIds());

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->getProxy()->setData('status', (int)$this->getProduct()->getStatus());
        $this->getProxy()->setData('price', (float)$this->getProduct()->getPrice());
        $this->getProxy()->setData('special_price', (float)$this->getProduct()->getSpecialPrice());
        $this->getProxy()->setData('special_price_from_date', $this->getProduct()->getSpecialFromDate());
        $this->getProxy()->setData('special_price_to_date', $this->getProduct()->getSpecialToDate());
        $this->getProxy()->setData('tier_price', $this->getProduct()->getTierPrice());
        $this->getProxy()->setData('default_qty', $this->getDefaultQty());

        $this->getProxy()->setAttributes($this->getTrackingAttributesWithValues());
    }

    protected function isAddingProductProcess()
    {
        return $this->getProductId() <= 0;
    }

    private function getProxy(): \M2E\OnBuy\Observer\Product\AddUpdate\Before\Proxy
    {
        if ($this->proxy !== null) {
            return $this->proxy;
        }

        $object = $this->proxyFactory->create();

        $object->setProductId($this->getProductId());
        $object->setStoreId($this->getStoreId());

        return $this->proxy = $object;
    }

    private function clearStoredProxy()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        unset(self::$proxyStorage[$key]);
    }

    private function storeProxy()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = \M2E\Core\Helper\Data::generateUniqueHash();
            $this->getEvent()->getProduct()->setData(self::BEFORE_EVENT_KEY, $key);
        }

        self::$proxyStorage[$key] = $this->getProxy();
    }

    protected function getDefaultQty()
    {
        if (!$this->magentoProductHelper->isGroupedType($this->getProduct()->getTypeId())) {
            return [];
        }

        $values = [];
        foreach ($this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct()) as $childProduct) {
            $values[$childProduct->getSku()] = $childProduct->getQty();
        }

        return $values;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    private function getTrackingAttributes(): array
    {
        $attributes = [];

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct()
            );
            $attributes = array_merge($attributes, $changeAttributeTracker->getTrackingAttributes());
        }

        return array_values(array_unique($attributes));
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    private function getTrackingAttributesWithValues(): array
    {
        $attributes = [];

        foreach ($this->getTrackingAttributes() as $attributeCode) {
            $attributes[$attributeCode] = $this->getMagentoProduct()->getAttributeValue($attributeCode);
        }

        return $attributes;
    }
}
