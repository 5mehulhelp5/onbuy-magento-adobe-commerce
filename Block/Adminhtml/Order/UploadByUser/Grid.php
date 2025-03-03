<?php

namespace M2E\OnBuy\Block\Adminhtml\Order\UploadByUser;

class Grid extends \M2E\OnBuy\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\Core\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory;
    private \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\OnBuy\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\OnBuy\Model\Account\Repository $accountRepository,
        \M2E\OnBuy\Model\Order\ReImport\ManagerFactory $reimportManagerFactory,
        \M2E\Core\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \M2E\OnBuy\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->customCollectionFactory = $customCollectionFactory;
        $this->uploadByUserManagerFactory = $reimportManagerFactory;
        $this->accountRepository = $accountRepository;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('orderUploadByUserPopupGrid');

        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->customCollectionFactory->create();

        foreach ($this->accountRepository->getAll() as $account) {
            $accountTitle = $account->getTitle();

            foreach ($account->getSites() as $site) {
                $manager = $this->uploadByUserManagerFactory->create($account, $site);

                $item = new \Magento\Framework\DataObject(
                    [
                        'title' => $accountTitle,
                        'site' => $site->getName(),
                        'site_id' => $site->getId(),
                        'from_date' => $manager->getFromDate() !== null
                            ? $manager->getFromDate()->format('Y-m-d H:i:s')
                            : null,
                        '_manager_' => $manager,
                        '_account_' => $account,
                    ]
                );
                $collection->addItem($item);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'align' => 'left',
                'width' => '300px',
                'type' => 'text',
                'sortable' => false,
                'index' => 'title',
            ]
        );

        $this->addColumn(
            'site',
            [
                'header' => __('Site'),
                'align' => 'left',
                'width' => '300px',
                'type' => 'text',
                'sortable' => false,
                'index' => 'site',
            ]
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('From Date'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'from_date',
                'sortable' => false,
                'type' => 'datetime',
                'format' => \IntlDateFormatter::MEDIUM,
                'frame_callback' => [$this, 'callbackColumnDate'],
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '80px',
                'type' => 'text',
                'align' => 'right',
                'sortable' => false,
                'frame_callback' => [$this, 'callbackColumnAction'],
            ]
        );

        return parent::_prepareColumns();
    }

    // ---------------------------------------

    public function callbackColumnDate($value, $row, $column, $isExport)
    {
        /** @var \M2E\OnBuy\Model\Order\ReImport\Manager $manager */
        $manager = $row['_manager_'];

        if ($manager->isEnabled()) {
            return $value;
        }

        /** @var \M2E\OnBuy\Model\Account $account */
        $account = $row['_account_'];
        $siteId = $row['site_id'];

        $inputId = "{$account->getId()}_{$siteId}";

        return <<<HTML
<script>
require([
    'mage/calendar'
], function () {
    jQuery('#{$inputId}').calendar({
        showsTime: true,
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:00',
        showButtonPanel: false
    })
})
</script>

<form id="{$inputId}_form" class="datetime-form">
    <input type="text"
           id="{$inputId}"
           name="{$inputId}"
           class="input-text admin__control-text required-entry validate-datetime" />
</form>
HTML;
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        /** @var \M2E\OnBuy\Model\Order\ReImport\Manager $manager */
        $manager = $row['_manager_'];

        /** @var \M2E\OnBuy\Model\Account $account */
        $account = $row['_account_'];
        $siteId = $row['site_id'];

        $data = [
            'label' => $manager->isEnabled()
                ? __('Cancel')
                : __('Reimport'),

            'onclick' => $manager->isEnabled()
                ? "UploadByUserObj.resetUpload({$account->getId()}, {$siteId})"
                : "UploadByUserObj.configureUpload({$account->getId()}, {$siteId})",

            'class' => 'action primary',
        ];

        $state = '';
        if ($manager->isEnabled()) {
            $inProgressText = __('(in progress)');
            $state = <<<HTML
<br/>
<span style="color: orange; font-style: italic;">$inProgressText</span>
HTML;
        }

        $button = $this->getLayout()->createBlock(\M2E\OnBuy\Block\Adminhtml\Magento\Button::class)->setData(
            $data
        );

        return $button->toHtml() . $state;
    }

    // ----------------------------------------

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/order_uploadByUser/getPopupGrid');
    }
}
