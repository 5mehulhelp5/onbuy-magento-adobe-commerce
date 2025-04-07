<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\Listing as ListingResource;

class Listing extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'listing';

    public const INSTRUCTION_TYPE_PRODUCT_ADDED = 'listing_product_added';
    public const INSTRUCTION_INITIATOR_ADDING_PRODUCT = 'adding_product_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER = 'listing_product_moved_from_other';
    public const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER = 'moving_product_from_other_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING = 'listing_product_moved_from_listing';
    public const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING = 'moving_product_from_listing_to_listing';

    public const INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING = 'listing_product_remap_from_listing';

    public const INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW = 'change_listing_store_view';
    public const INSTRUCTION_INITIATOR_CHANGED_LISTING_STORE_VIEW = 'changed_listing_store_view';

    public const CONDITION_NEW = 'new';
    public const CONDITION_REFURBISHED_DIAMOND = 'diamond';
    public const CONDITION_REFURBISHED_PLATINUM = 'platinum';
    public const CONDITION_REFURBISHED_GOLD = 'gold';
    public const CONDITION_REFURBISHED_SILVER = 'silver';
    public const CONDITION_REFURBISHED_BRONZE = 'bronze';
    public const CONDITION_REFURBISHED = 'refurbished-ungraded';

    public const CREATE_LISTING_SESSION_DATA = 'onbuy_listing_create';

    public const REQUIRED_POLICIES = [
        'Description' => ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID
    ];

    private \M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Site $site;
    private \M2E\OnBuy\Model\Policy\SellingFormat $templateSellingFormat;
    private \M2E\OnBuy\Model\Policy\Synchronization $templateSynchronization;
    private \M2E\OnBuy\Model\Policy\Shipping $templateShipping;
    private \M2E\OnBuy\Model\Policy\Description $templateDescription;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;
    private \M2E\OnBuy\Model\Product\Repository $listingProductRepository;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatTemplateRepository;
    private \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationTemplateRepository;
    private \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingTemplateRepository;
    private \M2E\OnBuy\Model\Policy\Description\Repository $templateDescriptionRepository;

    public function __construct(
        \M2E\OnBuy\Model\Product\Repository $listingProductRepository,
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \M2E\OnBuy\Model\Policy\SellingFormat\Repository $sellingFormatTemplateRepository,
        \M2E\OnBuy\Model\Policy\Synchronization\Repository $synchronizationTemplateRepository,
        \M2E\OnBuy\Model\Policy\Shipping\Repository $shippingTemplateRepository,
        \M2E\OnBuy\Model\Policy\Description\Repository $templateDescriptionRepository,
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
        $this->listingProductRepository = $listingProductRepository;
        $this->accountRepository = $accountRepository;
        $this->sellingFormatTemplateRepository = $sellingFormatTemplateRepository;
        $this->synchronizationTemplateRepository = $synchronizationTemplateRepository;
        $this->shippingTemplateRepository = $shippingTemplateRepository;
        $this->templateDescriptionRepository = $templateDescriptionRepository;
        $this->siteRepository = $siteRepository;
    }

    // ----------------------------------------

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\Listing::class);
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

    // ----------------------------------------

    /**
     * @return \M2E\OnBuy\Model\Product[]
     */
    public function getProducts(): array
    {
        $products = $this->listingProductRepository->findByListing($this);
        foreach ($products as $product) {
            $product->initListing($this);
        }

        return $products;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTemplateSellingFormat(): Policy\SellingFormat
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->templateSellingFormat)) {
            $this->templateSellingFormat = $this->sellingFormatTemplateRepository
                ->get($this->getTemplateSellingFormatId());
        }

        return $this->templateSellingFormat;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTemplateSynchronization(): Policy\Synchronization
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->templateSynchronization)) {
            $this->templateSynchronization = $this->synchronizationTemplateRepository
                ->get($this->getTemplateSynchronizationId());
        }

        return $this->templateSynchronization;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTemplateDescription(): Policy\Description
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->templateDescription)) {
            $this->templateDescription = $this->templateDescriptionRepository
                ->get($this->getTemplateDescriptionId());
        }

        return $this->templateDescription;
    }

    /**
     * @throws \M2E\OnBuy\Model\Exception\Logic
     */
    public function getTemplateShipping(): ?Policy\Shipping
    {
        if ($this->getTemplateShippingId() === null) {
            return null;
        }
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->templateShipping)) {
            $this->templateShipping = $this->shippingTemplateRepository
                ->get($this->getTemplateShippingId());
        }

        return $this->templateShipping;
    }

    public function hasTemplateShipping(): bool
    {
        return !empty($this->getTemplateShippingId());
    }

    public function isAllRequiredPoliciesExist(): bool
    {
        foreach (self::REQUIRED_POLICIES as $policy) {
            if (!$this->hasTemplatePolicyId($policy)) {
                return false;
            }
        }

        return true;
    }

    private function hasTemplatePolicyId(string $policy): bool
    {
        return (bool)$this->getData($policy);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ListingResource::COLUMN_TITLE);
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_ACCOUNT_ID);
    }

    public function getSiteId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_SITE_ID);
    }

    public function getSite(): \M2E\OnBuy\Model\Site
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->site)) {
            return $this->site;
        }

        return $this->site = $this->siteRepository->get($this->getSiteId());
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_STORE_ID);
    }

    public function getCreateDate()
    {
        return $this->getData(ListingResource::COLUMN_CREATE_DATE);
    }

    public function getUpdateDate()
    {
        return $this->getData(ListingResource::COLUMN_UPDATE_DATE);
    }

    public function setTemplateSellingFormatId(int $sellingFormatTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $sellingFormatTemplateId);
    }

    public function getTemplateSellingFormatId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID);
    }

    public function setTemplateDescriptionId(int $descriptionTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $descriptionTemplateId);
    }

    public function getTemplateDescriptionId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID);
    }

    public function setTemplateSynchronizationId(int $synchronizationTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $synchronizationTemplateId);
    }

    public function getTemplateSynchronizationId(): int
    {
        return (int)$this->getData(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID);
    }

    public function getCondition(): string
    {
        return $this->getData(ListingResource::COLUMN_CONDITION);
    }

    public function setCondition(string $condition): void
    {
        $this->setData(ListingResource::COLUMN_CONDITION, $condition);
    }

    public function getConditionNote(): string
    {
        return (string)$this->getData(ListingResource::COLUMN_CONDITION_NOTE);
    }

    public function setConditionNote(string $conditionNote): void
    {
        $this->setData(ListingResource::COLUMN_CONDITION_NOTE, $conditionNote);
    }
    public function setTemplateShippingId(?int $shippingTemplateId): void
    {
        $this->setData(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $shippingTemplateId);
    }

    public function getTemplateShippingId(): ?int
    {
        $value = $this->getData(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID);
        if (empty($value)) {
            return null;
        }

        return (int) $value;
    }

    // ---------------------------------------

    public function getAdditionalData(): array
    {
        $data = $this->getData(ListingResource::COLUMN_ADDITIONAL_DATA);
        if ($data === null) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setAdditionalData(array $additionalData): void
    {
        $this->setData(
            ListingResource::COLUMN_ADDITIONAL_DATA,
            json_encode($additionalData, JSON_THROW_ON_ERROR),
        );
    }

    public function setStoreId(int $id): void
    {
        $this->setData(ListingResource::COLUMN_STORE_ID, $id);
    }
}
