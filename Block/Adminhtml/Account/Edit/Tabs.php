<?php

namespace M2E\OnBuy\Block\Adminhtml\Account\Edit;

use M2E\OnBuy\Model\Account\Settings\Order as OrderSettings;
use M2E\OnBuy\Model\Account\Settings\UnmanagedListings as UnmanagedListingsSettings;

class Tabs extends \M2E\OnBuy\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_GENERAL = 'general';
    public const TAB_ID_LISTING_OTHER = 'listingOther';
    public const TAB_ID_ORDER = 'order';
    public const TAB_ID_INVOICES_AND_SHIPMENTS = 'invoices_and_shipments';

    private ?\M2E\OnBuy\Model\Account $account;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \M2E\OnBuy\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        $this->accountUrlHelper = $accountUrlHelper;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();

        $this->setId('onbuyAccountEditTabs');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        /** @var \M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\General $generalTabBlock */
        $generalTabBlock = $this
            ->getLayout()
            ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\General::class, '', [
                'account' => $this->account,
            ]);

        $this->addTab(
            self::TAB_ID_GENERAL,
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $generalTabBlock->toHtml(),
            ],
        );

        if ($this->isAccountExist()) {
            $listingOtherTabContent = $this
                ->getLayout()
                ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\UnmanagedListing::class, '', [
                    'account' => $this->account,
                ]);
            $this->addTab(
                self::TAB_ID_LISTING_OTHER,
                [
                    'label' => __('Unmanaged Listings'),
                    'title' => __('Unmanaged Listings'),
                    'content' => $listingOtherTabContent->toHtml(),
                ],
            );
        }

        if ($this->isAccountExist()) {
            /** @var \M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\Order $orderTabBlock */
            $orderTabBlock = $this
                ->getLayout()
                ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\Order::class, '', [
                    'account' => $this->account,
                ]);

            $this->addTab(
                self::TAB_ID_ORDER,
                [
                    'label' => __('Orders'),
                    'title' => __('Orders'),
                    'content' => $orderTabBlock->toHtml(),
                ],
            );
        }

        if ($this->isAccountExist()) {
            /** @var \M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\InvoicesAndShipments $invoicesAndShipmentsTabBlock */
            $invoicesAndShipmentsTabBlock = $this
                ->getLayout()
                ->createBlock(\M2E\OnBuy\Block\Adminhtml\Account\Edit\Tabs\InvoicesAndShipments::class, '', [
                    'account' => $this->account,
                ]);
            $this->addTab(
                self::TAB_ID_INVOICES_AND_SHIPMENTS,
                [
                    'label' => __('Invoices & Shipments'),
                    'title' => __('Invoices & Shipments'),
                    'content' => $invoicesAndShipmentsTabBlock->toHtml(),
                ],
            );
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_GENERAL));

        $this->jsUrl->addUrls(
            [
                'formSubmit' => $this->accountUrlHelper->getSaveUrl(
                    ['_current' => true, 'id' => $this->getRequest()->getParam('id')]
                ),
            ],
        );

        $this->jsPhp->addConstants(
            [
                'Account\Settings\UnmanagedListings::MAPPING_TITLE_MODE_NONE' => UnmanagedListingsSettings::MAPPING_TITLE_MODE_NONE,
                'Account\Settings\UnmanagedListings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\UnmanagedListings::MAPPING_SKU_MODE_NONE' => UnmanagedListingsSettings::MAPPING_SKU_MODE_NONE,
                'Account\Settings\UnmanagedListings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\UnmanagedListings::MAPPING_OPC_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_OPC_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\UnmanagedListings::MAPPING_OPC_MODE_NONE' => UnmanagedListingsSettings::MAPPING_OPC_MODE_NONE,
                'Account\Settings\Order::TAX_MODE_MIXED' => OrderSettings::TAX_MODE_MIXED,
                'Account\Settings\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT' => OrderSettings::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT,
                'Account\Settings\Order::CUSTOMER_MODE_GUEST' => OrderSettings::CUSTOMER_MODE_GUEST,
                'Account\Settings\Order::NUMBER_SOURCE_MAGENTO' => OrderSettings::NUMBER_SOURCE_MAGENTO,
                'Account\Settings\Order::CUSTOMER_MODE_NEW' => OrderSettings::CUSTOMER_MODE_NEW,
                'Account\Settings\Order::CUSTOMER_MODE_PREDEFINED' => OrderSettings::CUSTOMER_MODE_PREDEFINED,
                'Account\Settings\Order::LISTINGS_STORE_MODE_DEFAULT' => OrderSettings::LISTINGS_STORE_MODE_DEFAULT,
                'Account\Settings\Order::LISTINGS_OTHER_PRODUCT_MODE_IGNORE' => OrderSettings::LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
                'Account\Settings\Order::NUMBER_SOURCE_CHANNEL' => OrderSettings::NUMBER_SOURCE_CHANNEL,
                'Account\Settings\Order::LISTINGS_STORE_MODE_CUSTOM' => OrderSettings::LISTINGS_STORE_MODE_CUSTOM,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT' => OrderSettings::ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_PROCESSING' => OrderSettings::ORDERS_STATUS_MAPPING_PROCESSING,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_SHIPPED' => OrderSettings::ORDERS_STATUS_MAPPING_SHIPPED,
            ],
        );

        return parent::_beforeToHtml();
    }

    private function isAccountExist(): bool
    {
        return $this->account !== null;
    }
}
