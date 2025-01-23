<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\Product as ProductResource;

/**
 * @method \M2E\OnBuy\Model\Product\Action\Configurator getActionConfigurator()
 * @method setActionConfigurator(\M2E\OnBuy\Model\Product\Action\Configurator $configurator)
 */
class Product extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    public const ACTION_LIST = 1;
    public const ACTION_RELIST = 2;
    public const ACTION_REVISE = 3;
    public const ACTION_STOP = 4;
    public const ACTION_DELETE = 5;

    public const STATUS_NOT_LISTED = 0;
    public const STATUS_LISTED = 2;
    public const STATUS_INACTIVE = 8;
    public const STATUS_BLOCKED = 6;

    public const STATUS_CHANGER_UNKNOWN = 0;
    public const STATUS_CHANGER_SYNCH = 1;
    public const STATUS_CHANGER_USER = 2;
    public const STATUS_CHANGER_COMPONENT = 3;
    public const STATUS_CHANGER_OBSERVER = 4;

    public const MOVING_LISTING_OTHER_SOURCE_KEY = 'moved_from_listing_other_id';

    public const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    public const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED = 'channel_qty_changed';
    public const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED = 'channel_price_changed';
    public const INSTRUCTION_TYPE_VARIANT_SKU_REMOVED = 'variant_sku_removed';

    private \M2E\OnBuy\Model\Listing $listing;
    private \M2E\OnBuy\Model\Magento\Product\Cache $magentoProductModel;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\OnBuy\Model\Product\DataProvider $dataProvider;
    private \M2E\OnBuy\Model\Product\DataProviderFactory $dataProviderFactory;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\OnBuy\Model\Product\DataProviderFactory $dataProviderFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->listingRepository = $listingRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ProductResource::class);
    }

    // ----------------------------------------

    public function create(Listing $listing, int $magentoProductId): self
    {
        $this
            ->setListingId($listing->getId())
            ->setMagentoProductId($magentoProductId)
            ->setStatusNotListed(self::STATUS_CHANGER_USER);

        $this->initListing($listing);

        return $this;
    }

    public function fillFromUnmanagedProduct(\M2E\OnBuy\Model\UnmanagedProduct $unmanagedProduct): self
    {
        $this->setChannelProductId($unmanagedProduct->getChannelProductId())
             ->setStatus($unmanagedProduct->getStatus(), self::STATUS_CHANGER_COMPONENT)
             ->setOnlineTitle($unmanagedProduct->getTitle())
             ->setOnlineSku($unmanagedProduct->getSku())
             ->setOnlineGroupSku($unmanagedProduct->getGroupSku())
             ->setProductEncodedId($unmanagedProduct->getProductEncodedId())
             ->setIdentifiers($unmanagedProduct->getIdentifiers())
             ->setOnlinePrice($unmanagedProduct->getPrice())
             ->setOnlineQty($unmanagedProduct->getQty())
             ->setProductLinkOnChannel($unmanagedProduct->getProductUrl())
             ->setOpc($unmanagedProduct->getOpc());

        $additionalData = $this->getAdditionalData();
        $additionalData[self::MOVING_LISTING_OTHER_SOURCE_KEY] = $unmanagedProduct->getId();

        $this->setAdditionalData($additionalData);

        return $this;
    }

    // ----------------------------------------

    public function initListing(\M2E\OnBuy\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\OnBuy\Model\Listing
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listing)) {
            return $this->listing;
        }

        return $this->listing = $this->listingRepository->get($this->getListingId());
    }

    public function getAccount(): Account
    {
        return $this->getListing()->getAccount();
    }

    public function getDataProvider(): Product\DataProvider
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->dataProvider)) {
            return $this->dataProvider;
        }

        return $this->dataProvider = $this->dataProviderFactory->create($this);
    }

    public function getMagentoProduct(): \M2E\OnBuy\Model\Magento\Product\Cache
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->magentoProductModel)) {
            $this->magentoProductModel = $this->magentoProductFactory->create();
            $this->magentoProductModel->setProductId($this->getMagentoProductId());
            $this->magentoProductModel->setStoreId($this->getListing()->getStoreId());
            $this->magentoProductModel->setStatisticId($this->getId());
        }

        return $this->magentoProductModel;
    }

    // ----------------------------------------
    public function getListingId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_LISTING_ID);
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getChannelProductId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_CHANNEL_PRODUCT_ID);
    }

    public function getOnlineSku(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_SKU);
    }

    public function setOnlineSku(string $onlineSku): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_SKU, $onlineSku);

        return $this;
    }

    public function getOnlineGroupSku(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_GROUP_SKU);
    }

    public function setOnlineGroupSku(?string $onlineGroupSku): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_GROUP_SKU, $onlineGroupSku);

        return $this;
    }

    public function getOpc(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_OPC);
    }

    public function setOpc(string $opc): self
    {
        $this->setData(ProductResource::COLUMN_OPC, $opc);

        return $this;
    }

    public function getProductEncodedId(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_PRODUCT_ENCODED_ID);
    }

    public function setProductEncodedId(string $encodedId): self
    {
        $this->setData(ProductResource::COLUMN_PRODUCT_ENCODED_ID, $encodedId);

        return $this;
    }

    public function getIdentifiers(): array
    {
        $identifiers = $this->getData(ProductResource::COLUMN_IDENTIFIERS);

        if (empty($identifiers)) {
            return [];
        }

        return json_decode($identifiers, false);
    }

    public function setIdentifiers(array $identifiers): self
    {
        $this->setData(ProductResource::COLUMN_IDENTIFIERS, json_encode($identifiers));

        return $this;
    }

    public function getOnlineQty(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_ONLINE_QTY);
    }

    // ---------------------------------------

    public function isStatusNotListed(): bool
    {
        return $this->getStatus() === self::STATUS_NOT_LISTED;
    }

    public function isStatusBlocked(): bool
    {
        return $this->getStatus() === self::STATUS_BLOCKED;
    }

    public function isStatusListed(): bool
    {
        return $this->getStatus() === self::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === self::STATUS_INACTIVE;
    }

    public function setStatusListed(int $channelProductId, int $changer): self
    {
        $this
            ->setStatus(self::STATUS_LISTED, $changer)
            ->setChannelProductId($channelProductId);

        return $this;
    }

    public function setStatusNotListed(int $changer): self
    {
        $this->setStatus(self::STATUS_NOT_LISTED, $changer)
             ->setData(ProductResource::COLUMN_CHANNEL_PRODUCT_ID, null)
             ->setData(ProductResource::COLUMN_ONLINE_TITLE, null)
             ->setData(ProductResource::COLUMN_ONLINE_QTY, null);

        return $this;
    }

    public function setStatusInactive(int $changer): self
    {
        $this->setStatus(self::STATUS_INACTIVE, $changer);

        return $this;
    }

    public function setStatusBlocked(int $changer): self
    {
        $this->setStatus(self::STATUS_BLOCKED, $changer);

        return $this;
    }

    public function setStatus(int $status, int $changer): self
    {
        $this->setData(ProductResource::COLUMN_STATUS, $status)
             ->setStatusChanger($changer)
             ->setData(
                 ProductResource::COLUMN_STATUS_CHANGE_DATE,
                 \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
             );

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_STATUS);
    }

    // ----------------------------------------

    public function isStatusChangerUser(): bool
    {
        return $this->getStatusChanger() === self::STATUS_CHANGER_USER;
    }

    public function getStatusChanger(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_STATUS_CHANGER);
    }

    // ---------------------------------------

    public function setAdditionalData(array $value): self
    {
        $this->setData(ProductResource::COLUMN_ADDITIONAL_DATA, json_encode($value));

        return $this;
    }

    public function getAdditionalData(): array
    {
        $value = $this->getData(ProductResource::COLUMN_ADDITIONAL_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    // ---------------------------------------

    public function isListable(): bool
    {
        return (
                $this->isStatusNotListed()
                || $this->isStatusInactive()
            ) && !$this->isStatusBlocked();
    }

    public function isRelistable(): bool
    {
        return $this->isStatusInactive()
            && !$this->isStatusBlocked();
    }

    public function isRevisable(): bool
    {
        return $this->isStatusListed() && !$this->isStatusBlocked();
    }

    public function isStoppable(): bool
    {
        return $this->isStatusListed()
            && !$this->isStatusBlocked();
    }

    public function isRetirable(): bool
    {
        return (
                $this->isStatusListed()
                || $this->isStatusInactive()
            ) && !$this->isStatusBlocked();
    }

    public function isRemovableFromChannel(): bool
    {
        return !empty($this->getOpc());
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getSellingFormatTemplate(): \M2E\OnBuy\Model\Policy\SellingFormat
    {
        return $this->getListing()->getTemplateSellingFormat();
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getSynchronizationTemplate(): \M2E\OnBuy\Model\Policy\Synchronization
    {
        return $this->getListing()->getTemplateSynchronization();
    }

    // ---------------------------------------

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getSellingFormatTemplateSource(): \M2E\OnBuy\Model\Policy\SellingFormat\Source
    {
        return $this->getSellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    public function getOnlineTitle(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_TITLE);
    }

    public function getCurrencyCode(): string
    {
        return $this->getListing()->getSite()->getCurrencyCode();
    }

    // ---------------------------------------

    public function setOnlineQty(int $value): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_QTY, $value);

        return $this;
    }

    public function getOnlinePrice(): ?float
    {
        return (float)$this->getData(ProductResource::COLUMN_ONLINE_PRICE);
    }

    public function setOnlinePrice(float $value): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_PRICE, $value);

        return $this;
    }

    // ---------------------------------------

    public function changeListing(\M2E\OnBuy\Model\Listing $listing): self
    {
        $this->setListingId($listing->getId());
        $this->initListing($listing);

        return $this;
    }

    private function setListingId(int $listingId): self
    {
        $this->setData(ProductResource::COLUMN_LISTING_ID, $listingId);

        return $this;
    }

    private function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(ProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    private function setChannelProductId(int $productId): self
    {
        $this->setData(ProductResource::COLUMN_CHANNEL_PRODUCT_ID, $productId);

        return $this;
    }

    public function setOnlineTitle(string $onlineTitle): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_TITLE, $onlineTitle);

        return $this;
    }

    // ----------------------------------------

    private function setStatusChanger(int $statusChanger): self
    {
        $this->validateStatusChanger($statusChanger);

        $this->setData(ProductResource::COLUMN_STATUS_CHANGER, $statusChanger);

        return $this;
    }

    // ---------------

    public static function getStatusTitle(int $status): string
    {
        $statuses = [
            self::STATUS_NOT_LISTED => (string)__('Not Listed'),
            self::STATUS_LISTED => (string)__('Active'),
            self::STATUS_BLOCKED => (string)__('Incomplete'),
            self::STATUS_INACTIVE => (string)__('Inactive'),
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    // ----------------------------------------

    private function validateStatusChanger(int $changer): void
    {
        $allowed = [
            self::STATUS_CHANGER_SYNCH,
            self::STATUS_CHANGER_USER,
            self::STATUS_CHANGER_COMPONENT,
            self::STATUS_CHANGER_OBSERVER,
        ];

        if (!in_array($changer, $allowed)) {
            throw new \M2E\OnBuy\Model\Exception\Logic(sprintf('Status changer %s not valid.', $changer));
        }
    }

    // ----------------------------------------

    public function getProductLinkOnChannel(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_PRODUCT_URL);
    }

    public function setProductLinkOnChannel(string $value): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_PRODUCT_URL, $value);

        return $this;
    }

    public function hasBlockingByError(): bool
    {
        $rawDate = $this->getData(ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE);
        if (empty($rawDate)) {
            return false;
        }

        $lastBlockingDate = \M2E\Core\Helper\Date::createDateGmt($rawDate);
        $twentyFourHoursAgoDate = \M2E\Core\Helper\Date::createCurrentGmt()->modify('-24 hour');

        return $lastBlockingDate->getTimestamp() > $twentyFourHoursAgoDate->getTimestamp();
    }

    public function removeBlockingByError(): self
    {
        $this->setData(ProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE, null);

        return $this;
    }
}
