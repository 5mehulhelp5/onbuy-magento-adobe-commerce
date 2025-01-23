<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\Site as SiteResource;

class Site extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'site';

    private Account\Repository $accountRepository;

    private \M2E\OnBuy\Model\Account $account;

    public function __construct(
        Account\Repository $accountRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->accountRepository = $accountRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\Site::class);
    }

    public function create(
        \M2E\OnBuy\Model\Account $account,
        int $siteId,
        string $name,
        string $countryCode,
        string $currencyCode
    ): self {
        $this
            ->setData(SiteResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(SiteResource::COLUMN_SITE_ID, $siteId)
            ->setData(SiteResource::COLUMN_NAME, $name)
            ->setData(SiteResource::COLUMN_COUNTRY_CODE, $countryCode)
            ->setData(SiteResource::COLUMN_CURRENCY_CODE, $currencyCode);

        $this->initAccount($account);

        return $this;
    }

    public function initAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getAccount(): \M2E\OnBuy\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        $account = $this->accountRepository->find($this->getAccountId());

        if ($account === null) {
            throw new \M2E\OnBuy\Model\Exception\Logic('Account must be created');
        }

        return $this->account = $account;
    }

    // ----------------------------------------

    public function getAccountId(): int
    {
        return $this->getData(SiteResource::COLUMN_ACCOUNT_ID);
    }

    public function getName(): string
    {
        return $this->getData(SiteResource::COLUMN_NAME);
    }

    public function getSiteId(): int
    {
        return (int)$this->getData(SiteResource::COLUMN_SITE_ID);
    }

    public function getCountryCode(): string
    {
        return $this->getData(SiteResource::COLUMN_COUNTRY_CODE);
    }

    public function getCurrencyCode(): string
    {
        return $this->getData(SiteResource::COLUMN_CURRENCY_CODE);
    }

    public function setInventoryLastSyncDate(\DateTimeInterface $date): self
    {
        $this->setData(SiteResource::COLUMN_INVENTORY_LAST_SYNC_DATE, $date);

        return $this;
    }

    public function getInventoryLastSyncDate(): ?\DateTime
    {
        $value = $this->getData(SiteResource::COLUMN_INVENTORY_LAST_SYNC_DATE);
        if (empty($value)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }

    public function resetInventoryLastSyncDate(): self
    {
        $this->setData(SiteResource::COLUMN_INVENTORY_LAST_SYNC_DATE, null);

        return $this;
    }

    public function getOrdersLastSyncDate(): ?\DateTimeImmutable
    {
        $value = $this->getData(SiteResource::COLUMN_ORDER_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createImmutableDateGmt($value);
    }

    public function setOrdersLastSyncDate(\DateTimeInterface $date): self
    {
        $this->setData(SiteResource::COLUMN_ORDER_LAST_SYNC, $date->format('Y-m-d H:i:s'));

        return $this;
    }
}
