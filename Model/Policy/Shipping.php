<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Policy;

use M2E\OnBuy\Model\ResourceModel\Policy\Shipping as ShippingResource;

class Shipping extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel implements PolicyInterface
{
    public const HANDLING_TIME_MODE_NOT_SET = 0;
    public const HANDLING_TIME_MODE_VALUE = 1;
    public const HANDLING_TIME_MODE_ATTRIBUTE = 2;

    private \M2E\OnBuy\Model\Account\Repository $accountRepository;
    private \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\OnBuy\Model\Account $account;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry
        );

        $this->accountRepository = $accountRepository;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\Policy\Shipping::class);
    }

    public function create(
        int $accountId,
        int $siteId,
        string $title,
        int $deliveryTemplateId,
        int $handlingTimeMode,
        int $handlingTime,
        string $handlingTimeAttribute
    ): self {
        $this->setData(ShippingResource::COLUMN_ACCOUNT_ID, $accountId)
             ->setData(ShippingResource::COLUMN_SITE_ID, $siteId)
             ->setTitle($title)
             ->setDeliveryTemplateId($deliveryTemplateId)
             ->setHandlingTimeMode($handlingTimeMode)
             ->setHandlingTime($handlingTime)
             ->setHandlingTimeAttribute($handlingTimeAttribute);

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->setData(ShippingResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ShippingResource::COLUMN_TITLE);
    }

    public function getNick(): string
    {
        return \M2E\OnBuy\Model\Policy\Manager::TEMPLATE_SHIPPING;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_ACCOUNT_ID);
    }

    public function getDeliveryTemplateId(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_DELIVERY_TEMPLATE_ID);
    }

    public function setDeliveryTemplateId(int $deliveryTemplateId): self
    {
        $this->setData(ShippingResource::COLUMN_DELIVERY_TEMPLATE_ID, $deliveryTemplateId);

        return $this;
    }

    public function setHandlingTime(int $handlingTime): self
    {
        $this->setData(ShippingResource::COLUMN_HANDLING_TIME, $handlingTime);

        return $this;
    }

    public function getHandlingTime(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME);
    }

    public function setHandlingTimeMode(int $handlingTimeMode): self
    {
        $this->setData(ShippingResource::COLUMN_HANDLING_TIME_MODE, $handlingTimeMode);

        return $this;
    }

    public function getHandlingTimeMode(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME_MODE);
    }

    public function setHandlingTimeAttribute(string $handlingTimeAttribute): self
    {
        $this->setData(ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE, $handlingTimeAttribute);

        return $this;
    }

    public function getHandlingTimeAttribute(): string
    {
        return (string)$this->getData(ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE);
    }

    public function getAccount(): \M2E\OnBuy\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    public function isLocked(): bool
    {
        return (bool)$this
            ->listingCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\OnBuy\Model\ResourceModel\Listing::COLUMN_TEMPLATE_SHIPPING_ID,
                $this->getId()
            )
            ->getSize();
    }
}
