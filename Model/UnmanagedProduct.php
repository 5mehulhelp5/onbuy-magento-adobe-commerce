<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;

class UnmanagedProduct extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    private \M2E\OnBuy\Model\Account $account;
    private ?\M2E\OnBuy\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Magento\Product\CacheFactory $productCacheFactory;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Magento\Product\CacheFactory $productCacheFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
        );

        $this->accountRepository = $accountRepository;
        $this->productCacheFactory = $productCacheFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(UnmanagedProductResource::class);
    }

    public function create(
        int $accountId,
        int $siteId,
        int $channelProductId,
        int $status,
        string $title,
        string $productUrl,
        string $sku,
        ?string $groupSku,
        string $opc,
        string $productEncodedId,
        array $identifiers,
        float $price,
        string $currencyCode,
        int $handlingTime,
        int $qty,
        string $condition,
        array $conditionNotes,
        int $deliveryWeight,
        int $deliveryTemplateId
    ): self {
        $this
            ->setData(UnmanagedProductResource::COLUMN_ACCOUNT_ID, $accountId)
            ->setData(UnmanagedProductResource::COLUMN_SITE_ID, $siteId)
            ->setData(UnmanagedProductResource::COLUMN_CHANNEL_PRODUCT_ID, $channelProductId)
            ->setStatus($status)
            ->setTitle($title)
            ->setData(UnmanagedProductResource::COLUMN_PRODUCT_URL, $productUrl)
            ->setData(UnmanagedProductResource::COLUMN_SKU, $sku)
            ->setData(UnmanagedProductResource::COLUMN_GROUP_SKU, $groupSku)
            ->setData(UnmanagedProductResource::COLUMN_OPC, $opc)
            ->setData(UnmanagedProductResource::COLUMN_PRODUCT_ENCODED_ID, $productEncodedId)
            ->setIdentifiers($identifiers)
            ->setPrice($price)
            ->setData(UnmanagedProductResource::COLUMN_CURRENCY_CODE, $currencyCode)
            ->setHandlingTime($handlingTime)
            ->setQty($qty)
            ->setCondition($condition)
            ->setConditionNotes($conditionNotes)
            ->setDeliveryWeight($deliveryWeight)
            ->setDeliveryTemplateId($deliveryTemplateId);

        return $this;
    }

    // ----------------------------------------

    public function getAccount(): \M2E\OnBuy\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    // ---------------------------------------

    /**
     * @return \M2E\OnBuy\Model\Magento\Product\Cache
     * @throws \M2E\OnBuy\Model\Exception
     */
    public function getMagentoProduct(): ?\M2E\OnBuy\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if (!$this->hasMagentoProductId()) {
            throw new \M2E\OnBuy\Model\Exception('Product id is not set');
        }

        return $this->magentoProductModel = $this->productCacheFactory->create()
                                                                      ->setStoreId($this->getRelatedStoreId())
                                                                      ->setProductId($this->getMagentoProductId());
    }

    // ----------------------------------------

    public function getAccountId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_ACCOUNT_ID);
    }

    public function getSiteId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_SITE_ID);
    }

    // ----------------------------------------

    public function hasMagentoProductId(): bool
    {
        return !empty($this->getMagentoProductId());
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function mapToMagentoProduct(int $magentoProductId): void
    {
        $this->setData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function unmapFromMagentoProduct(): void
    {
        $this->setData(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, null);
    }

    // ----------------------------------------

    public function getChannelProductId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_CHANNEL_PRODUCT_ID);
    }

    // ----------------------------------------

    public function isStatusListed(): bool
    {
        return $this->getStatus() === \M2E\OnBuy\Model\Product::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === \M2E\OnBuy\Model\Product::STATUS_INACTIVE;
    }

    public function setStatus(int $status): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_STATUS, $status);

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_STATUS);
    }

    // ----------------------------------------

    public function setTitle(string $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_TITLE, $value);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(UnmanagedProductResource::COLUMN_TITLE);
    }

    public function getProductUrl(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_PRODUCT_URL);
    }

    public function getSku(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_SKU);
    }

    public function getGroupSku(): ?string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_GROUP_SKU);
    }

    public function getOpc(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_OPC);
    }

    public function getProductEncodedId(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_PRODUCT_ENCODED_ID);
    }

    public function setIdentifiers(array $identifiers): self
    {
        return $this->setData(
            UnmanagedProductResource::COLUMN_IDENTIFIERS,
            json_encode($identifiers)
        );
    }

    public function getIdentifiers(): array
    {
        $identifiers = $this->getData(UnmanagedProductResource::COLUMN_IDENTIFIERS);
        if (empty($identifiers)) {
            return [];
        }

        return json_decode($identifiers, true);
    }

    // ----------------------------------------

    public function setPrice(float $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_PRICE, $value);

        return $this;
    }

    public function getPrice(): float
    {
        return (float)$this->getData(UnmanagedProductResource::COLUMN_PRICE);
    }

    public function getCurrencyCode(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_CURRENCY_CODE);
    }

    public function setHandlingTime(int $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_HANDLING_TIME, $value);

        return $this;
    }

    public function getHandlingTime(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_HANDLING_TIME);
    }

    public function setQty(int $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_QTY, $value);

        return $this;
    }

    public function getQty(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_QTY);
    }

    // ----------------------------------------

    public function setCondition(string $condition): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_CONDITIONS, $condition);

        return $this;
    }

    public function getCondition(): string
    {
        return $this->getData(UnmanagedProductResource::COLUMN_CONDITIONS);
    }

    public function setConditionNotes(array $notes): self
    {
        return $this->setData(
            UnmanagedProductResource::COLUMN_CONDITIONS_NOTES,
            json_encode($notes)
        );
    }

    public function getConditionNotes(): array
    {
        $notes = $this->getData(UnmanagedProductResource::COLUMN_CONDITIONS_NOTES);
        if (empty($notes)) {
            return [];
        }

        return json_decode($notes, true);
    }

    // ---------------------------------------

    public function setDeliveryWeight(int $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_DELIVERY_WEIGHT, $value);

        return $this;
    }

    public function getDeliveryWeight(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_DELIVERY_WEIGHT);
    }

    public function setDeliveryTemplateId(int $value): self
    {
        $this->setData(UnmanagedProductResource::COLUMN_DELIVERY_TEMPLATE_ID, $value);

        return $this;
    }

    public function getDeliveryTemplateId(): int
    {
        return (int)$this->getData(UnmanagedProductResource::COLUMN_DELIVERY_TEMPLATE_ID);
    }

    // ---------------------------------------

    public function isListingCorrectForMove(\M2E\OnBuy\Model\Listing $listing): bool
    {
        return $listing->getAccountId() === $this->getAccountId()
            && $listing->getSiteId() === $this->getSiteId();
    }

    public function getRelatedStoreId(): int
    {
        return $this->getAccount()->getUnmanagedListingSettings()->getRelatedStoreForSiteId($this->getSiteId());
    }

    // ----------------------------------------

    public function updateFromChannel(\M2E\OnBuy\Model\Channel\Product $channelProduct): bool
    {
        $isChanged = false;

        if ($this->getStatus() !== $channelProduct->getStatus()) {
            $this->setStatus($channelProduct->getStatus());

            $isChanged = true;
        }

        if ($this->getTitle() !== $channelProduct->getTitle()) {
            $this->setTitle($channelProduct->getTitle());

            $isChanged = true;
        }

        if ($this->getQty() !== $channelProduct->getQty()) {
            $this->setQty($channelProduct->getQty());

            $isChanged = true;
        }

        if ($this->getPrice() !== $channelProduct->getPrice()) {
            $this->setPrice($channelProduct->getPrice());

            $isChanged = true;
        }

        if ($this->getHandlingTime() !== $channelProduct->getHandlingTime()) {
            $this->setHandlingTime($channelProduct->getHandlingTime());

            $isChanged = true;
        }

        if ($this->getCondition() !== $channelProduct->getCondition()) {
            $this->setCondition($channelProduct->getCondition());

            $isChanged = true;
        }

        if ($this->getConditionNotes() !== $channelProduct->getConditionNotes()) {
            $this->setConditionNotes($channelProduct->getConditionNotes());

            $isChanged = true;
        }

        if ($this->getDeliveryWeight() !== $channelProduct->getDeliveryWeight()) {
            $this->setDeliveryWeight($channelProduct->getDeliveryWeight());

            $isChanged = true;
        }

        if ($this->getDeliveryTemplateId() !== $channelProduct->getDeliveryTemplateId()) {
            $this->setDeliveryTemplateId($channelProduct->getDeliveryTemplateId());

            $isChanged = true;
        }

        return $isChanged;
    }
}
