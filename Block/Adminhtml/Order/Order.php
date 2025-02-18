<?php

namespace M2E\OnBuy\Block\Adminhtml\Order;

use M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractContainer;
use M2E\OnBuy\Controller\Adminhtml\Order\AssignToMagentoProduct;

class Order extends AbstractContainer
{
    /** @var \M2E\OnBuy\Helper\Data */
    private $dataHelper;
    private \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory;
    private \M2E\OnBuy\Model\ResourceModel\Account\Collection $accountCollection;
    private \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper;

    public function __construct(
        \M2E\OnBuy\Model\Account\Ui\UrlHelper $accountUrlHelper,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\OnBuy\Helper\Data $dataHelper,
        \M2E\OnBuy\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->accountUrlHelper = $accountUrlHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('onBuyOrder');
        $this->_controller = 'adminhtml_onBuy_order';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addOrderSettingButton();

        $this->addButton(
            'upload_by_user',
            [
                'label' => __('Order Reimport'),
                'onclick' => 'UploadByUserObj.openPopup()',
                'class' => 'action-primary',
            ],
        );
    }

    protected function _prepareLayout()
    {
        $this->appendHelpBlock([
            'content' => __(
                '<p>In this section, you can find the list of the Orders imported ' .
                'from %channel_title.</p><p>An %channel_title Order, for which Magento Order is created, ' .
                'contains a value in <strong>Magento Order #</strong> column of the grid. ' .
                'You can find the corresponding Magento Order in Sales > Orders section of your Magento</p><br>' .
                '<p>To manage the imported %channel_title Orders, you can use Mass Action options available in the ' .
                'Actions bulk: Reserve QTY, Cancel QTY Reserve, Mark Order(s) as Shipped or Paid and Resend ' .
                'Shipping Information.</p><br><p>Also, you can view the detailed Order information by ' .
                'clicking on the appropriate row of the grid.</p><br><p><strong>Note:</strong> Automatic creation ' .
                'of Magento Orders, Invoices, and Shipments is performed in accordance with the Order ' .
                'settings specified in <br> <strong>Account Settings (%channel_title ' .
                '> Configuration > Accounts)</strong>. </p>',
                [
                    'channel_title' => \M2E\OnBuy\Helper\Module::getChannelTitle(),
                ]
            ),
        ]);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                \M2E\OnBuy\Block\Adminhtml\Order\Grid::class,
                'order.grid'
            )
        );

        $this->setPageActionsBlock(\M2E\OnBuy\Block\Adminhtml\Order\PageActions::class);

        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Order\Item\Edit::class)->toHtml() .
            parent::getGridHtml();
    }

    protected function _beforeToHtml()
    {
        $this->jsPhp->addConstants(
            [
                '\M2E\OnBuy\Controller\Adminhtml\Order\AssignToMagentoProduct::MAPPING_PRODUCT' => AssignToMagentoProduct::MAPPING_PRODUCT,
                '\M2E\OnBuy\Controller\Adminhtml\Order\AssignToMagentoProduct::MAPPING_OPTIONS' => AssignToMagentoProduct::MAPPING_OPTIONS,
            ]
        );

        $this->js->addRequireJs(
            ['upload' => 'OnBuy/Order/UploadByUser'],
            <<<JS
UploadByUserObj = new UploadByUser('orderUploadByUserPopupGrid');
JS,
        );

        $this->jsUrl->addUrls([
            'order_uploadByUser/configure' => $this->getUrl('m2e_onbuy/order_uploadByUser/configure/'),
            'order_uploadByUser/getPopupGrid' => $this->getUrl('m2e_onbuy/order_uploadByUser/getPopupGrid/'),
            'order_uploadByUser/getPopupHtml' => $this->getUrl('m2e_onbuy/order_uploadByUser/getPopupHtml/'),
            'order_uploadByUser/reset' => $this->getUrl('m2e_onbuy/order_uploadByUser/reset/'),
        ]);

        $this->jsTranslator->addTranslations(
            [
                'Order Reimport' => __('Order Reimport'),
                'Order importing in progress.' => __('Order importing in progress.'),
                'Order importing is canceled.' => __('Order importing is canceled.'),
            ],
        );

        return parent::_beforeToHtml();
    }

    private function addOrderSettingButton(): void
    {
        $accountId = $this->getAccountId();
        $url = $accountId ? $this->getSettingButtonUrl($accountId) : '';
        $classAttribute = $accountId ? 'action-primary' : 'drop_down edit_default_settings_drop_down primary';
        $className = !$accountId ? \M2E\OnBuy\Block\Adminhtml\Magento\Button\DropDown::class : null;

        $dropDownOptions = $this->getAccountSettingsDropDownItems($accountId);

        if ($dropDownOptions === []) {
            return;
        }

        $this->addButton(
            'order_settings',
            [
                'label' => __('Order Settings'),
                'onclick' => $url,
                'class' => $classAttribute,
                'class_name' => $className,
                'options' => $dropDownOptions,
            ],
        );
    }

    private function getAccountId(): int
    {
        return $this->getAccountIdFromRequest() ?: $this->getAccountIdFromCollection();
    }

    private function getAccountIdFromRequest(): int
    {
        return (int)$this->getRequest()->getParam('account');
    }

    private function getAccountIdFromCollection(): int
    {
        $accountCollection = $this->getAccountCollection();

        return $accountCollection->getSize() < 2 ? (int)$accountCollection->getFirstItem()->getId() : 0;
    }

    private function getAccountCollection(): \M2E\OnBuy\Model\ResourceModel\Account\Collection
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->accountCollection)) {
            $collection = $this->accountCollectionFactory->create();

            $this->accountCollection = $collection;
        }

        return $this->accountCollection;
    }

    private function getSettingButtonUrl(int $accountId): string
    {
        return sprintf(
            "window.open('%s', '_blank')",
            $this->accountUrlHelper->getEditUrl($accountId, ['tab' => 'order'])
        );
    }

    private function getAccountSettingsDropDownItems(int $accountId): array
    {
        $dropDownItems = [];

        if (!$accountId) {
            /** @var \M2E\OnBuy\Model\Account $accountItem */
            foreach ($this->getAccountCollection() as $accountItem) {
                $accountTitle = $this->filterManager->truncate(
                    $accountItem->getTitle(),
                    ['length' => 15],
                );

                $dropDownItems[] = [
                    'label' => $accountTitle,
                    'onclick' => $this->getSettingButtonUrl($accountItem->getId()),
                ];
            }
        }

        return $dropDownItems;
    }
}
