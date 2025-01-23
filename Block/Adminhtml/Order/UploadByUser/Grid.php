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
            $manager = $this->uploadByUserManagerFactory->create($account);

            $item = new \Magento\Framework\DataObject(
                [
                    'title' => $account->getTitle(),
                    'identifier' => $manager->getIdentifier(),
                    'from_date' => $manager->getFromDate() !== null
                        ? $manager->getFromDate()->format('Y-m-d H:i:s')
                        : null,
                    'to_date' => $manager->getToDate() !== null
                        ? $manager->getToDate()->format('Y-m-d H:i:s')
                        : null,
                    '_manager_' => $manager,
                    '_account_' => $account,
                ]
            );
            $collection->addItem($item);
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
            'to_date',
            [
                'header' => __('To Date'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'to_date',
                'type' => 'datetime',
                'sortable' => false,
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

    //########################################

    public function callbackColumnDate($value, $row, $column, $isExport)
    {
        /** @var \M2E\OnBuy\Model\Order\ReImport\Manager $manager */
        $manager = $row['_manager_'];

        if ($manager->isEnabled()) {
            return $value;
        }

        /** @var \M2E\OnBuy\Model\Account $account */
        $account = $row['_account_'];

        return <<<HTML
<script>

require([
    'mage/calendar'
], function () {
    jQuery('#{$account->getId()}_{$column->getIndex()}').calendar({
        showsTime: true,
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:00',
        showButtonPanel: false
    })
})

</script>

<form id="{$account->getId()}_{$column->getIndex()}_form">
    <input type="text" id="{$account->getId()}_{$column->getIndex()}" name="{$account->getId()}_{$column->getIndex()}"
           class="input-text admin__control-text required-entry validate-date" />
</form>
HTML;
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        /** @var \M2E\OnBuy\Model\Order\ReImport\Manager $manager */
        $manager = $row['_manager_'];

        /** @var \M2E\OnBuy\Model\Account $account */
        $account = $row['_account_'];

        $data = [
            'label' => $manager->isEnabled()
                ? __('Cancel')
                : __('Reimport'),

            'onclick' => $manager->isEnabled()
                ? "UploadByUserObj.resetUpload({$account->getId()})"
                : "UploadByUserObj.configureUpload({$account->getId()})",

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
}
