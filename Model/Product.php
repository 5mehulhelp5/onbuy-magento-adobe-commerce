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

    public const SEARCH_STATUS_NONE = 0;
    public const SEARCH_STATUS_COMPLETED = 1;

    private \M2E\OnBuy\Model\Listing $listing;
    private ?\M2E\OnBuy\Model\Category\Dictionary $categoryDictionary = null;
    private \M2E\OnBuy\Model\Magento\Product\Cache $magentoProductModel;
    private \M2E\OnBuy\Model\Listing\Repository $listingRepository;
    private \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\OnBuy\Model\Product\DataProvider $dataProvider;
    private \M2E\OnBuy\Model\Product\DataProviderFactory $dataProviderFactory;
    private \M2E\OnBuy\Model\Product\Description\RendererFactory $descriptionRendererFactory;
    private \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private ?string $runtimeMagentoSku = null;

    public function __construct(
        \M2E\OnBuy\Model\Listing\Repository $listingRepository,
        \M2E\OnBuy\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\OnBuy\Model\Product\DataProviderFactory $dataProviderFactory,
        \M2E\OnBuy\Model\Product\Description\RendererFactory $descriptionRendererFactory,
        \M2E\OnBuy\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->listingRepository = $listingRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->dataProviderFactory = $dataProviderFactory;
        $this->descriptionRendererFactory = $descriptionRendererFactory;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ProductResource::class);
    }

    // ----------------------------------------

    public function create(Listing $listing, int $magentoProductId, ?string $opc): self
    {
        $this
            ->setListingId($listing->getId())
            ->setMagentoProductId($magentoProductId)
            ->setStatusNotListed(self::STATUS_CHANGER_USER);

        $this->initListing($listing);

        if ($opc !== null) {
            $this->setOpc($opc);
        }

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

    public function setMagentoSku(string $magentoSku): void
    {
        $this->runtimeMagentoSku = $magentoSku;
    }

    public function getMagentoSku(): ?string
    {
        return $this->runtimeMagentoSku;
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

    public function hasOpc(): bool
    {
        return (string)$this->getData(ProductResource::COLUMN_OPC) !== '';
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
             ->setData(ProductResource::COLUMN_ONLINE_SKU, null)
             ->setData(ProductResource::COLUMN_OPC, null)
             ->setData(ProductResource::COLUMN_PRODUCT_ENCODED_ID, null)
             ->setData(ProductResource::COLUMN_ONLINE_GROUP_SKU, null)
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

        $this->removeBlockingByError();
        $this->removeMarkAsListingOnChannelStatus();

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

    //@todo to revise conditions
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
        return !empty($this->getOnlineSku());
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

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getShippingTemplate(): ?\M2E\OnBuy\Model\Policy\Shipping
    {
        return $this->getListing()->getTemplateShipping();
    }

    // ---------------------------------------

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getSellingFormatTemplateSource(): \M2E\OnBuy\Model\Policy\SellingFormat\Source
    {
        return $this->getSellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return \M2E\OnBuy\Model\Policy\Description
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getDescriptionTemplate(): \M2E\OnBuy\Model\Policy\Description
    {
        return $this->getListing()->getTemplateDescription();
    }

    public function getRenderedDescription(): string
    {
        return $this->descriptionRendererFactory
            ->create($this)
            ->parseTemplate($this->getDescriptionTemplateSource()->getDescription());
    }

    /**
     * @return \M2E\OnBuy\Model\Policy\Description\Source
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getDescriptionTemplateSource(): \M2E\OnBuy\Model\Policy\Description\Source
    {
        return $this->getDescriptionTemplate()->getSource($this->getMagentoProduct());
    }

    public function getCategoryDictionary(): Category\Dictionary
    {
        if (isset($this->categoryDictionary)) {
            return $this->categoryDictionary;
        }

        if (!$this->hasCategoryTemplate()) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Category was not selected.');
        }

        return $this->categoryDictionary = $this->categoryDictionaryRepository->get($this->getTemplateCategoryId());
    }

    public function hasCategoryTemplate(): bool
    {
        return !empty($this->getData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID));
    }

    public function getOnlineTitle(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_TITLE);
    }

    public function setOnlineTitle(string $onlineTitle): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_TITLE, $onlineTitle);

        return $this;
    }

    public function getOnlineDescription(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_DESCRIPTION);
    }

    public function setOnlineDescription(string $description): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_DESCRIPTION, $description);

        return $this;
    }

    public function getOnlineMainImage(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_MAIN_IMAGE);
    }

    public function setOnlineMainImage(string $image): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_MAIN_IMAGE, $image);

        return $this;
    }

    public function getOnlineAdditionalImages(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_ADDITIONAL_IMAGES);
    }

    public function setOnlineAdditionalImages(string $images): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_ADDITIONAL_IMAGES, $images);

        return $this;
    }

    public function getOnlineCategoryId(): ?int
    {
        $value = $this->getData(ProductResource::COLUMN_ONLINE_CATEGORY_ID);

        return $value !== null ? (int)$value : null;
    }

    public function setOnlineCategoryId(int $categoryId): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CATEGORY_ID, $categoryId);

        return $this;
    }

    public function getOnlineCategoryAttributesData(): string
    {
        return (string)$this->getData(ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA);
    }

    public function setOnlineCategoryAttributesData(string $data): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA, $data);

        return $this;
    }

    public function getOnlineDeliveryTemplateId(): ?int
    {
        $value = $this->getData(ProductResource::COLUMN_ONLINE_DELIVERY_TEMPLATE_ID);

        return $value !== null ? (int)$value : null;
    }

    public function setOnlineDeliveryTemplateId(int $deliveryTemplateId): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_DELIVERY_TEMPLATE_ID, $deliveryTemplateId);

        return $this;
    }

    public function getOnlineHandlingTime(): ?int
    {
        $value = $this->getData(ProductResource::COLUMN_ONLINE_HANDLING_TIME);

        return $value !== null ? (int)$value : null;
    }

    public function setOnlineHandlingTime(?int $handlingTime): self
    {
        $this->setData(ProductResource::COLUMN_ONLINE_HANDLING_TIME, $handlingTime);

        return $this;
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

    public function setTemplateCategoryId(int $id): self
    {
        $this->setData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID, $id);
        $this->resetValidationData();

        return $this;
    }

    public function getTemplateCategoryId(): int
    {
        return (int)$this->getData(ProductResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }

    public function setChannelProductId(int $productId): self
    {
        $this->setData(ProductResource::COLUMN_CHANNEL_PRODUCT_ID, $productId);

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

    public function isProductCreator(): bool
    {
        return (bool)$this->getData(ProductResource::COLUMN_IS_PRODUCT_CREATOR);
    }

    public function setProductCreator(bool $value): void
    {
        $this->setData(ProductResource::COLUMN_IS_PRODUCT_CREATOR, (int)$value);
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

    // ----------------------------------------

    public function isProductMarketAsListingOnChannel(): bool
    {
        if (!$this->isStatusNotListed()) {
            return false;
        }

        $value = (bool)$this->getData(ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL);
        if (!$value) {
            return false;
        }

        $date = $this->getData(ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL_START_DATE);
        if (empty($date)) {
            return false;
        }

        $date = \M2E\Core\Helper\Date::createDateGmt($date);

        return $date > \M2E\Core\Helper\Date::createCurrentGmt()->modify('-24 hour');
    }

    public function markProductAsListingOnChannel(): void
    {
        $this->setData(ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL, true);
        $this->setData(
            ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL_START_DATE,
            \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );
    }

    private function removeMarkAsListingOnChannelStatus(): void
    {
        $this->setData(ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL, false);
        $this->setData(ProductResource::COLUMN_LIST_IN_PROGRESS_ON_CHANNEL_START_DATE, null);
    }

    public function isInvalidCategoryAttributes(): bool
    {
        $value = $this->getData(ProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES);

        return $value === null ? false : !$value;
    }

    public function markCategoryAttributesAsValid(): void
    {
        $this->setCategoryAttributesValid(true);
        $this->setCategoryAttributesErrors([]);
    }

    /**
     * @param string[] $errors
     *
     * @return void
     */
    public function markCategoryAttributesAsInvalid(array $errors): void
    {
        $this->setCategoryAttributesValid(false);
        $this->setCategoryAttributesErrors($errors);
    }

    private function setCategoryAttributesValid(bool $isValid): void
    {
        $this->setData(ProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES, $isValid);
    }

    private function setCategoryAttributesErrors(array $errors): void
    {
        $value = null;
        if (!empty($errors)) {
            $value = json_encode($errors);
        }

        $this->setData(ProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS, $value);
    }

    private function resetValidationData(): void
    {
        $this->setData(ProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES, null);
        $this->setData(ProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS, null);
    }
}
