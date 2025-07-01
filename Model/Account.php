<?php

namespace M2E\OnBuy\Model;

use M2E\OnBuy\Model\ResourceModel\Account as AccountResource;

class Account extends \M2E\OnBuy\Model\ActiveRecord\AbstractModel
{
    /**
     * @var \M2E\OnBuy\Model\Site[]
     */
    private array $sites;
    private Account\Settings\UnmanagedListings $unmanagedListingSettings;
    private Account\Settings\Order $ordersSettings;
    private Account\Settings\InvoicesAndShipment $invoiceAndShipmentSettings;
    private \M2E\OnBuy\Model\Site\Repository $siteRepository;

    public function __construct(
        \M2E\OnBuy\Model\Site\Repository $siteRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
        );
        $this->siteRepository = $siteRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\OnBuy\Model\ResourceModel\Account::class);
    }

    public function create(
        string $title,
        string $identifier,
        string $serverHash,
        bool $isTest,
        \M2E\OnBuy\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\OnBuy\Model\Account\Settings\Order $orderSettings,
        \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): self {
        $this
            ->setTitle($title)
            ->setData(AccountResource::COLUMN_IDENTIFIER, $identifier)
            ->setData(AccountResource::COLUMN_SERVER_HASH, $serverHash)
            ->setData(AccountResource::COLUMN_IS_TEST, $isTest)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        return $this;
    }

    /**
     * @param Site[] $sites
     *
     * @return $this
     */
    public function initSites(array $sites): self
    {
        $this->sites = $sites;
        foreach ($this->sites as $site) {
            $site->initAccount($this);
        }

        return $this;
    }

    public function getSites(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->sites)) {
            return $this->sites;
        }

        $this->sites = $this->siteRepository->findForAccount($this->getId());
        foreach ($this->sites as $site) {
            $site->initAccount($this);
        }

        return $this->sites;
    }

    // ----------------------------------------

    public function setTitle(string $title): self
    {
        $this->setData(AccountResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle()
    {
        return $this->getData(AccountResource::COLUMN_TITLE);
    }

    public function getServerHash()
    {
        return $this->getData(AccountResource::COLUMN_SERVER_HASH);
    }

    public function getIdentifier(): string
    {
        return (string)$this->getData(AccountResource::COLUMN_IDENTIFIER);
    }

    public function isTest(): bool
    {
        return (bool)(int)$this->getData(AccountResource::COLUMN_IS_TEST);
    }

    // ----------------------------------------

    public function setUnmanagedListingSettings(
        \M2E\OnBuy\Model\Account\Settings\UnmanagedListings $settings
    ): self {
        $this->unmanagedListingSettings = $settings;
        $this
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION, (int)$settings->isSyncEnabled())
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE, (int)$settings->isMappingEnabled())
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                json_encode(
                    [
                        'sku' => $settings->getMappingBySkuSettings(),
                        'title' => $settings->getMappingByTitleSettings(),
                        'item_id' => $settings->getMappingByItemIdSettings(),
                    ],
                ),
            )
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORES,
                json_encode($settings->getRelatedStores()),
            );

        return $this;
    }

    public function getUnmanagedListingSettings(): \M2E\OnBuy\Model\Account\Settings\UnmanagedListings
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->unmanagedListingSettings)) {
            return $this->unmanagedListingSettings;
        }

        $mappingSettings = $this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS);
        $mappingSettings = json_decode($mappingSettings, true);

        $settings = new \M2E\OnBuy\Model\Account\Settings\UnmanagedListings();

        return $this->unmanagedListingSettings = $settings
            ->createWithSync((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION))
            ->createWithMapping((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE))
            ->createWithMappingSettings(
                $mappingSettings['sku'] ?? [],
                $mappingSettings['title'] ?? [],
                $mappingSettings['item_id'] ?? [],
            )
            ->createWithRelatedStores(
                json_decode($this->getData(AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORES), true),
            );
    }

    public function setOrdersSettings(\M2E\OnBuy\Model\Account\Settings\Order $settings): self
    {
        $this->ordersSettings = $settings;

        $data = $settings->toArray();

        $this->setData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS, json_encode($data));

        return $this;
    }

    public function getOrdersSettings(): \M2E\OnBuy\Model\Account\Settings\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->ordersSettings)) {
            return $this->ordersSettings;
        }

        $data = json_decode($this->getData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS), true);

        $settings = new \M2E\OnBuy\Model\Account\Settings\Order();

        return $this->ordersSettings = $settings->createWith($data);
    }

    public function setInvoiceAndShipmentSettings(
        \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment $settings
    ): self {
        $this->invoiceAndShipmentSettings = $settings;

        $this
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE, (int)$settings->isCreateMagentoInvoice())
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT, (int)$settings->isCreateMagentoShipment());

        return $this;
    }

    public function getInvoiceAndShipmentSettings(): \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->invoiceAndShipmentSettings)) {
            return $this->invoiceAndShipmentSettings;
        }

        $settings = new \M2E\OnBuy\Model\Account\Settings\InvoicesAndShipment();

        return $this->invoiceAndShipmentSettings = $settings
            ->createWithMagentoInvoice((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE))
            ->createWithMagentoShipment((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT));
    }

    public function getCreateData(): \DateTimeImmutable
    {
        $value = $this->getData(AccountResource::COLUMN_CREATE_DATE);

        return \M2E\Core\Helper\Date::createImmutableDateGmt($value);
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersStatusMappingDefault(): bool
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['order_status_mapping', 'mode'],
            \M2E\OnBuy\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        return $setting == \M2E\OnBuy\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }
}
